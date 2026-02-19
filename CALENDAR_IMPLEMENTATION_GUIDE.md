# Guide d'Implémentation - Calendrier Notion-like

## ✅ Implémentation Complète

Tous les composants ont été créés et sont prêts à l'utilisation !

## 📁 Fichiers Créés/Modifiés

### Backend

1. **`src/Entity/Event.php`** - Entité améliorée avec nouveaux champs
   - `startTime`, `endTime`, `color`, `category`, `notes`, `allDay`, `reminderMinutes`

2. **`src/Service/CalendarService.php`** - Service métier complet
   - Gestion des événements, recherche, filtrage, groupement

3. **`src/Controller/Api/CalendarApiController.php`** - API REST complète
   - GET, POST, PUT, DELETE pour les événements
   - Endpoints pour recherche, catégories, événements à venir

4. **`src/Controller/CalendarController.php`** - Contrôleur de vue
   - Route `/calendar` pour afficher le calendrier

### Frontend

5. **`templates/calendar/notion_calendar.html.twig`** - Vue calendrier complète
   - Calendrier FullCalendar avec vues multiples
   - Panneau latéral avec événements à venir
   - Modal CRUD complet
   - Recherche en temps réel

6. **`templates/front/base.html.twig`** - Menu mis à jour
   - Lien "Calendrier" ajouté dans la navigation

### Documentation

7. **`CALENDAR_ARCHITECTURE.md`** - Documentation complète de l'architecture

## 🚀 Utilisation

### Accéder au Calendrier

1. Connectez-vous à l'application
2. Cliquez sur "Calendrier" dans le menu de gauche
3. Vous verrez le calendrier avec toutes les fonctionnalités

### Créer un Événement

1. Cliquez sur le bouton "Nouveau" en haut à droite
2. OU cliquez directement sur une date dans le calendrier
3. Remplissez le formulaire :
   - Titre (obligatoire)
   - Date (obligatoire)
   - Heure de début/fin (optionnel)
   - Catégorie et couleur
   - Description, lieu, statut, priorité
4. Cliquez sur "Enregistrer"

### Modifier un Événement

1. Cliquez sur un événement dans le calendrier
2. Le modal s'ouvre avec les données pré-remplies
3. Modifiez les champs souhaités
4. Cliquez sur "Enregistrer"

### Déplacer un Événement

1. Cliquez et glissez un événement vers une nouvelle date/heure
2. L'événement est automatiquement mis à jour

### Redimensionner un Événement

1. Survolez le bord inférieur d'un événement
2. Cliquez et glissez pour modifier la durée
3. L'événement est automatiquement mis à jour

### Rechercher des Événements

1. Utilisez la barre de recherche dans le panneau latéral
2. Tapez au moins 3 caractères
3. Les résultats s'affichent automatiquement dans le calendrier

### Voir les Événements à Venir

Le panneau latéral affiche automatiquement les 5 prochains événements avec :
- Heure de l'événement
- Titre
- Catégorie et statut
- Clic pour ouvrir les détails

## 🔌 API REST - Exemples d'Utilisation

### Récupérer tous les événements

```bash
GET /api/calendar/events
```

### Récupérer les événements d'une plage de dates

```bash
GET /api/calendar/events?start=2026-02-01&end=2026-02-28
```

### Créer un événement

```bash
POST /api/calendar/events
Content-Type: application/json

{
  "title": "Réunion d'équipe",
  "date": "2026-02-18",
  "startTime": "2026-02-18T10:00:00",
  "endTime": "2026-02-18T11:00:00",
  "category": "Meeting",
  "color": "#3b82f6",
  "description": "Discussion sur les projets",
  "location": "Salle de conférence",
  "status": "Pending",
  "priority": "High",
  "allDay": false
}
```

### Mettre à jour un événement

```bash
PUT /api/calendar/events/1
Content-Type: application/json

{
  "title": "Réunion d'équipe - Modifié",
  "status": "In Progress"
}
```

### Supprimer un événement

```bash
DELETE /api/calendar/events/1
```

### Rechercher des événements

```bash
GET /api/calendar/search?q=réunion
```

### Récupérer les événements à venir

```bash
GET /api/calendar/upcoming?limit=10
```

### Récupérer les catégories

```bash
GET /api/calendar/categories
```

## 🎨 Vues Disponibles

1. **Vue Mensuelle** : Calendrier mensuel avec tous les événements
2. **Vue Hebdomadaire** : Vue semaine avec timeline horaire
3. **Vue Journalière** : Vue jour avec timeline horaire détaillée
4. **Vue Liste** : Liste chronologique des événements

## 🎯 Fonctionnalités Implémentées

✅ **Vues multiples** : Mois, Semaine, Jour, Liste  
✅ **CRUD complet** : Créer, Lire, Modifier, Supprimer  
✅ **Drag & Drop** : Déplacer les événements  
✅ **Resize** : Modifier la durée en redimensionnant  
✅ **Recherche** : Recherche en temps réel  
✅ **Panneau latéral** : Événements à venir et catégories  
✅ **Gestion des couleurs** : 6 couleurs prédéfinies  
✅ **Gestion des catégories** : Catégories personnalisées  
✅ **Heures précises** : Support des heures de début/fin  
✅ **Journée entière** : Support des événements journée entière  
✅ **API REST** : Endpoints complets pour intégration  
✅ **Sécurité** : Isolation des données par utilisateur  

## 📊 Structure des Données

### Format JSON d'un Événement

```json
{
  "id": 1,
  "title": "Réunion d'équipe",
  "description": "Discussion sur les projets",
  "type": "Meeting",
  "category": "Work",
  "color": "#3b82f6",
  "date": "2026-02-18",
  "startTime": "2026-02-18T10:00:00",
  "endTime": "2026-02-18T11:00:00",
  "duration": 60,
  "location": "Salle de conférence",
  "status": "In Progress",
  "priority": "High",
  "difficulty": 2,
  "notes": "Préparer l'ordre du jour",
  "allDay": false,
  "reminderMinutes": 15,
  "userId": 4
}
```

## 🔧 Configuration

### Couleurs Disponibles

- `#3b82f6` - Bleu (par défaut)
- `#6366f1` - Indigo
- `#10b981` - Vert
- `#ef4444` - Rouge
- `#8b5cf6` - Violet
- `#f59e0b` - Orange

### Catégories Prédéfinies

Les catégories sont créées dynamiquement selon les événements créés. Exemples :
- Blog Post
- Video
- Podcast
- Exam
- Course
- Meeting
- Task

## 🐛 Dépannage

### Le calendrier ne s'affiche pas

1. Vérifiez que vous êtes connecté
2. Videz le cache : `php bin/console cache:clear`
3. Vérifiez la console du navigateur pour les erreurs JavaScript

### Les événements ne se chargent pas

1. Vérifiez que l'API répond : `GET /api/calendar/events`
2. Vérifiez les permissions utilisateur
3. Vérifiez la connexion à la base de données

### Les modifications ne se sauvegardent pas

1. Vérifiez que les champs obligatoires sont remplis
2. Vérifiez la console du navigateur pour les erreurs
3. Vérifiez les logs Symfony : `var/log/dev.log`

## 📚 Ressources

- **FullCalendar Documentation** : https://fullcalendar.io/docs
- **API Documentation** : Voir `CALENDAR_ARCHITECTURE.md`
- **Architecture** : Voir `CALENDAR_ARCHITECTURE.md`

## 🎉 Prêt à Utiliser !

Le système de calendrier est maintenant complètement fonctionnel et prêt à être utilisé. Accédez à `/calendar` pour commencer !
