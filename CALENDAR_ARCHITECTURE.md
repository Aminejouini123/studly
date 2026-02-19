# Architecture du Calendrier - Système similaire à Notion

## 📋 Vue d'ensemble

Ce document décrit l'architecture complète du système de calendrier implémenté dans l'application Studly, conçu pour reproduire les fonctionnalités du calendrier Notion.

## 🏗️ Architecture Backend

### 1. Modèle de Données (Entity)

**Fichier:** `src/Entity/Event.php`

L'entité `Event` contient les champs suivants :

```php
- id: Identifiant unique
- title: Titre de l'événement
- description: Description détaillée
- type: Type d'événement (Blog Post, Video, Podcast, etc.)
- category: Catégorie personnalisée
- color: Couleur hexadécimale pour l'affichage
- date: Date de l'événement (DATE)
- startTime: Heure de début (DATETIME, nullable)
- endTime: Heure de fin (DATETIME, nullable)
- duration: Durée en minutes
- location: Lieu de l'événement
- status: Statut (Pending, In Progress, Completed)
- priority: Priorité (Low, Medium, High)
- difficulty: Niveau de difficulté (1-5)
- notes: Notes additionnelles (TEXT)
- allDay: Événement journée entière (BOOLEAN)
- reminderMinutes: Minutes avant rappel (INT, nullable)
- user: Relation ManyToOne vers User
- motivation: Relation OneToOne vers Motivation
- pomodoroSessions: Relation OneToMany vers PomodoroSession
```

### 2. Service Métier

**Fichier:** `src/Service/CalendarService.php`

Le service `CalendarService` encapsule toute la logique métier :

#### Méthodes principales :

- `getEventsForDateRange()`: Récupère les événements dans une plage de dates
- `getUpcomingEvents()`: Récupère les prochains événements (N événements)
- `searchEvents()`: Recherche d'événements par texte
- `getEventsGroupedByDate()`: Groupe les événements par date
- `getEventsByCategory()`: Filtre par catégorie
- `createEventFromArray()`: Crée un événement depuis un tableau
- `updateEventFromArray()`: Met à jour un événement depuis un tableau

### 3. API REST

**Fichier:** `src/Controller/Api/CalendarApiController.php`

#### Endpoints disponibles :

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `/api/calendar/events` | Liste tous les événements (avec filtres optionnels) |
| GET | `/api/calendar/events/{id}` | Récupère un événement spécifique |
| POST | `/api/calendar/events` | Crée un nouvel événement |
| PUT | `/api/calendar/events/{id}` | Met à jour un événement |
| DELETE | `/api/calendar/events/{id}` | Supprime un événement |
| GET | `/api/calendar/upcoming` | Récupère les événements à venir |
| GET | `/api/calendar/search` | Recherche d'événements |
| GET | `/api/calendar/categories` | Liste toutes les catégories |

#### Format de réponse JSON :

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Réunion d'équipe",
      "description": "Discussion sur les projets",
      "date": "2026-02-18",
      "startTime": "2026-02-18T10:00:00",
      "endTime": "2026-02-18T11:00:00",
      "color": "#3b82f6",
      "category": "Meeting",
      "status": "In Progress",
      "priority": "High",
      "allDay": false,
      "location": "Salle de conférence",
      "notes": "Préparer l'ordre du jour"
    }
  ],
  "count": 1
}
```

## 🎨 Architecture Frontend

### 1. Vue Calendrier Principal

**Fichier:** `templates/calendar/notion_calendar.html.twig`

#### Structure :

```
┌─────────────────────────────────────────────────┐
│  Toolbar (Vues: Mois/Semaine/Jour/Liste)      │
├─────────────────────────────────────────────────┤
│                                                 │
│  Calendrier FullCalendar                       │
│  - Vue mensuelle                                │
│  - Vue hebdomadaire                             │
│  - Vue journalière                              │
│  - Vue liste                                    │
│                                                 │
└─────────────────────────────────────────────────┘
┌──────────────────┐
│  Sidebar         │
│  - Recherche     │
│  - À venir      │
│  - Catégories   │
└──────────────────┘
```

### 2. Composants JavaScript

#### Fonctionnalités implémentées :

1. **Initialisation du calendrier**
   - Configuration FullCalendar
   - Chargement des événements via API
   - Gestion des vues (Mois, Semaine, Jour, Liste)

2. **CRUD d'événements**
   - Création via modal
   - Édition via modal
   - Suppression avec confirmation
   - Drag & drop pour déplacer
   - Resize pour modifier la durée

3. **Panneau latéral**
   - Affichage des événements à venir
   - Liste des catégories
   - Recherche en temps réel

4. **Modal d'événement**
   - Formulaire complet
   - Sélection de couleur
   - Gestion jour/journée entière
   - Validation côté client

## 🔄 Flux de Données

### Création d'événement :

```
1. Utilisateur clique sur "Nouveau" ou sur une date
   ↓
2. Modal s'ouvre avec formulaire vide
   ↓
3. Utilisateur remplit le formulaire
   ↓
4. JavaScript collecte les données
   ↓
5. POST /api/calendar/events avec JSON
   ↓
6. CalendarApiController → CalendarService
   ↓
7. Event créé et sauvegardé en DB
   ↓
8. Réponse JSON avec événement créé
   ↓
9. Calendrier se rafraîchit (refetchEvents)
   ↓
10. Panneau latéral se met à jour
```

### Mise à jour d'événement :

```
1. Utilisateur clique sur un événement
   ↓
2. Modal s'ouvre avec données pré-remplies
   ↓
3. Utilisateur modifie les champs
   ↓
4. PUT /api/calendar/events/{id} avec JSON
   ↓
5. CalendarApiController → CalendarService
   ↓
6. Event mis à jour en DB
   ↓
7. Réponse JSON avec événement mis à jour
   ↓
8. Calendrier se rafraîchit
```

## 🔐 Sécurité

### Contrôles d'accès :

1. **Authentification requise** : Tous les endpoints nécessitent `ROLE_USER`
2. **Isolation des données** : Les utilisateurs ne voient que leurs propres événements
3. **Vérification de propriété** : Avant modification/suppression, vérification que l'utilisateur est propriétaire
4. **CSRF Protection** : Les formulaires incluent des tokens CSRF

## 📊 Structure Modulaire

```
src/
├── Entity/
│   └── Event.php                    # Modèle de données
├── Repository/
│   └── EventRepository.php          # Requêtes personnalisées
├── Service/
│   └── CalendarService.php          # Logique métier
├── Controller/
│   ├── CalendarController.php       # Contrôleur de vue
│   └── Api/
│       └── CalendarApiController.php  # API REST
└── Form/
    └── EventType.php                # Formulaire Symfony

templates/
└── calendar/
    └── notion_calendar.html.twig    # Vue calendrier complète
```

## 🚀 Étapes d'Implémentation

### Étape 1 : Migration de la base de données

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Étape 2 : Vérification de l'API

Tester les endpoints avec :

```bash
# GET events
curl http://localhost:8000/api/calendar/events

# POST create
curl -X POST http://localhost:8000/api/calendar/events \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","date":"2026-02-18"}'

# GET upcoming
curl http://localhost:8000/api/calendar/upcoming
```

### Étape 3 : Accès au calendrier

Accéder à : `http://localhost:8000/calendar`

## 🎯 Fonctionnalités Implémentées

✅ Vue calendrier mensuelle  
✅ Vue calendrier hebdomadaire  
✅ Vue calendrier journalière  
✅ Vue liste  
✅ Création d'événements  
✅ Modification d'événements  
✅ Suppression d'événements  
✅ Drag & drop  
✅ Resize d'événements  
✅ Panneau latéral avec événements à venir  
✅ Recherche d'événements  
✅ Filtrage par catégorie  
✅ Gestion des couleurs  
✅ Gestion des heures précises  
✅ Événements journée entière  
✅ API REST complète  
✅ Synchronisation en temps réel  

## 📝 Notes Techniques

- **FullCalendar 6.1.10** : Bibliothèque JavaScript pour le calendrier
- **API REST** : Tous les endpoints retournent du JSON
- **Responsive** : Interface adaptée mobile et desktop
- **Performance** : Chargement paresseux des événements par plage de dates
- **Extensibilité** : Architecture modulaire permettant d'ajouter facilement de nouvelles fonctionnalités

## 🔮 Améliorations Futures

- [ ] Synchronisation avec calendriers externes (Google Calendar, Outlook)
- [ ] Notifications push pour les rappels
- [ ] Partage d'événements entre utilisateurs
- [ ] Répétition d'événements (quotidien, hebdomadaire, mensuel)
- [ ] Vue agenda avec timeline horaire détaillée
- [ ] Export iCal
- [ ] Intégration WebSocket pour mise à jour temps réel multi-utilisateurs

## 🏆 Bonnes Pratiques & Performance

### 1. Performance
- **Lazy Loading** : Le calendrier ne charge que les événements de la vue courante (plage de dates) via `getEventsForDateRange`.
- **Note** : Pour l'instant, `FullCalendar` gère le rendu DOM efficacement, mais avec des milliers d'événements, il faudra implémenter le "event constraint" côté serveur.
- **Caching** : Implémenter le cache HTTP sur les requêtes GET (ETags) pour éviter les re-téléchargements inutiles.

### 2. Sécurité
- **Validation** : Toujours valider les dates (start < end) côté backend.
- **Sanatization** : Échapper les titres et descriptions pour éviter les XSS (Symfony/Twig le fait par défaut, mais attention au JSON).
- **Authorization** : Vérifier systématiquement `e.user == currentUser` dans chaque endpoint API.

### 3. Scalabilité
- **Base de données** : Indexer les colonnes `date`, `startTime`, `user_id` et `status` pour optimiser les recherches.
- **Archives** : Prévoir un mécanisme d'archivage pour les événements passés de plus de 2 ans pour garder la table légère.

