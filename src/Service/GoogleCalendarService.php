<?php

namespace App\Service;

use App\Entity\User;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;

class GoogleCalendarService
{
    private Client $client;

    public function __construct(
        private string $googleClientId,
        private string $googleClientSecret,
    ) {
        $this->client = new Client();
        $this->client->setClientId($this->googleClientId);
        $this->client->setClientSecret($this->googleClientSecret);
    }

    private function getCalendarService(User $user): Calendar
    {
        $accessToken = $user->getGoogleAccessToken();
        $refreshToken = $user->getGoogleRefreshToken();
        
        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired()) {
            if ($refreshToken) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (!isset($newToken['error'])) {
                    $user->setGoogleAccessToken($newToken['access_token']);
                    if (isset($newToken['expires_in'])) {
                        $user->setGoogleTokenExpiresAt((new \DateTime())->modify('+' . $newToken['expires_in'] . ' seconds'));
                    }
                    // The entity needs to be flushed by the caller if it's updated here, 
                    // but for simplicity in this service we assume the caller handles the user state if needed.
                }
            }
        }

        return new Calendar($this->client);
    }

    public function listEvents(User $user, string $timeMin = 'now', string $timeMax = null, int $maxResults = 10, string $q = null): array
    {
        $service = $this->getCalendarService($user);
        $optParams = [
            'maxResults' => $maxResults,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => (new \DateTime($timeMin))->format(\DateTime::RFC3339),
        ];

        if ($timeMax) {
            $optParams['timeMax'] = (new \DateTime($timeMax))->format(\DateTime::RFC3339);
        }

        if ($q) {
            $optParams['q'] = $q;
        }

        $results = $service->events->listEvents('primary', $optParams);
        $events = [];

        foreach ($results->getItems() as $event) {
            $events[] = [
                'id' => $event->getId(),
                'summary' => $event->getSummary(),
                'description' => $event->getDescription(),
                'start' => $event->getStart()->getDateTime() ?: $event->getStart()->getDate(),
                'end' => $event->getEnd()->getDateTime() ?: $event->getEnd()->getDate(),
                'location' => $event->getLocation(),
            ];
        }

        return $events;
    }

    public function createEvent(User $user, array $data): array
    {
        $service = $this->getCalendarService($user);
        
        $event = new Event([
            'summary' => $data['summary'],
            'location' => $data['location'] ?? '',
            'description' => $data['description'] ?? '',
            'start' => ['dateTime' => $data['start'], 'timeZone' => 'Africa/Tunis'],
            'end' => ['dateTime' => $data['end'], 'timeZone' => 'Africa/Tunis'],
        ]);

        if (!empty($data['attendees'])) {
            $attendees = [];
            foreach ($data['attendees'] as $email) {
                $attendees[] = ['email' => $email];
            }
            $event->setAttendees($attendees);
        }

        $createdEvent = $service->events->insert('primary', $event);

        return [
            'id' => $createdEvent->getId(),
            'htmlLink' => $createdEvent->getHtmlLink(),
        ];
    }

    public function updateEvent(User $user, string $eventId, array $updates): array
    {
        $service = $this->getCalendarService($user);
        $event = $service->events->get('primary', $eventId);

        if (isset($updates['summary'])) $event->setSummary($updates['summary']);
        if (isset($updates['description'])) $event->setDescription($updates['description']);
        if (isset($updates['location'])) $event->setLocation($updates['location']);
        
        if (isset($updates['start'])) {
            $start = new EventDateTime();
            $start->setDateTime($updates['start']);
            $event->setStart($start);
        }
        
        if (isset($updates['end'])) {
            $end = new EventDateTime();
            $end->setDateTime($updates['end']);
            $event->setEnd($end);
        }

        $updatedEvent = $service->events->update('primary', $eventId, $event);

        return [
            'id' => $updatedEvent->getId(),
            'htmlLink' => $updatedEvent->getHtmlLink(),
        ];
    }

    public function deleteEvent(User $user, string $eventId): bool
    {
        $service = $this->getCalendarService($user);
        $service->events->delete('primary', $eventId);
        return true;
    }

    public function syncEvent(User $user, \App\Entity\Event $localEvent): string
    {
        $service = $this->getCalendarService($user);
        
        $gEvent = new \Google\Service\Calendar\Event();
        $gEvent->setSummary($localEvent->getTitle());
        $gEvent->setDescription($localEvent->getDescription());
        $gEvent->setLocation($localEvent->getLocation());

        // Handle dates
        $start = new \Google\Service\Calendar\EventDateTime();
        $end = new \Google\Service\Calendar\EventDateTime();

        if ($localEvent->getStartTime()) {
            $start->setDateTime($localEvent->getStartTime()->format(\DateTime::RFC3339));
        } else {
            $start->setDate($localEvent->getDate()->format('Y-m-d'));
        }

        if ($localEvent->getEndTime()) {
            $end->setDateTime($localEvent->getEndTime()->format(\DateTime::RFC3339));
        } else {
            // Default: 1 hour duration or just same day
            $endDate = clone $localEvent->getDate();
            $end->setDate($endDate->format('Y-m-d'));
        }

        $gEvent->setStart($start);
        $gEvent->setEnd($end);

        if ($localEvent->getGoogleEventId()) {
            // Update existing
            try {
                $updated = $service->events->update('primary', $localEvent->getGoogleEventId(), $gEvent);
                return $updated->getId();
            } catch (\Exception $e) {
                // If not found, maybe create a new one?
                if ($e->getCode() == 404) {
                    $created = $service->events->insert('primary', $gEvent);
                    return $created->getId();
                }
                throw $e;
            }
        } else {
            // Create new
            $created = $service->events->insert('primary', $gEvent);
            return $created->getId();
        }
    }

    public function getFreeSlots(User $user, string $timeMin, string $timeMax, int $durationMinutes = 60): array
    {
        $service = $this->getCalendarService($user);
        
        $request = new \Google\Service\Calendar\FreeBusyRequest();
        $request->setTimeMin((new \DateTime($timeMin))->format(\DateTime::RFC3339));
        $request->setTimeMax((new \DateTime($timeMax))->format(\DateTime::RFC3339));
        $request->setItems([['id' => 'primary']]);

        $query = $service->freebusy->query($request);
        $busySlots = $query->getCalendars()['primary']->getBusy();

        $start = new \DateTime($timeMin);
        $end = new \DateTime($timeMax);
        $freeSlots = [];
        $current = clone $start;

        foreach ($busySlots as $slot) {
            $slotStart = new \DateTime($slot->getStart());
            $slotEnd = new \DateTime($slot->getEnd());

            $diff = $current->diff($slotStart);
            $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

            if ($minutes >= $durationMinutes) {
                $freeSlots[] = [
                    'start' => $current->format(\DateTime::RFC3339),
                    'end' => $slotStart->format(\DateTime::RFC3339),
                    'duration' => $minutes
                ];
            }
            if ($slotEnd > $current) {
                $current = clone $slotEnd;
            }
        }

        // Check gap between last busy slot and timeMax
        $diff = $current->diff($end);
        $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
        if ($minutes >= $durationMinutes && $current < $end) {
            $freeSlots[] = [
                'start' => $current->format(\DateTime::RFC3339),
                'end' => $end->format(\DateTime::RFC3339),
                'duration' => $minutes
            ];
        }

        return $freeSlots;
    }
}
