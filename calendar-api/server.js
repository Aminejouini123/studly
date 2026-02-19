const express = require('express');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Base de données en mémoire
let events = {};

// Helpers
function formatDate(year, month, day) {
    return `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
}

function getDaysInMonth(year, month) {
    return new Date(year, month + 1, 0).getDate();
}

function getFirstDayIndex(year, month) {
    let day = new Date(year, month, 1).getDay();
    return day === 0 ? 6 : day - 1;
}

// Routes
app.get('/api/calendar/:year/:month', (req, res) => {
    const year = parseInt(req.params.year);
    const month = parseInt(req.params.month);

    if (isNaN(year) || isNaN(month) || month < 0 || month > 11) {
        return res.status(400).json({ error: 'Paramètres invalides. year (nombre), month (0-11)' });
    }

    const daysInMonth = getDaysInMonth(year, month);
    const firstDayIndex = getFirstDayIndex(year, month);

    const days = [];

    for (let i = 0; i < firstDayIndex; i++) {
        days.push({ day: null, empty: true });
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const dateKey = formatDate(year, month, d);
        days.push({
            day: d,
            date: dateKey,
            empty: false,
            events: events[dateKey] || []
        });
    }

    const totalCells = Math.ceil((firstDayIndex + daysInMonth) / 7) * 7;
    const remaining = totalCells - (firstDayIndex + daysInMonth);
    for (let i = 0; i < remaining; i++) {
        days.push({ day: null, empty: true });
    }

    res.json({
        year,
        month,
        monthName: new Date(year, month).toLocaleString('fr-FR', { month: 'long' }),
        days
    });
});

app.get('/api/events', (req, res) => {
    const { date } = req.query;
    if (!date || !/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return res.status(400).json({ error: 'Paramètre date requis et au format YYYY-MM-DD' });
    }
    res.json({ date, events: events[date] || [] });
});

app.post('/api/events', (req, res) => {
    const { date, title, description } = req.body;
    if (!date || !title) {
        return res.status(400).json({ error: 'date et title sont requis' });
    }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return res.status(400).json({ error: 'date doit être au format YYYY-MM-DD' });
    }

    const newEvent = {
        id: Date.now() + Math.random().toString(36).substr(2, 5),
        date,
        title,
        description: description || '',
        createdAt: new Date().toISOString()
    };

    if (!events[date]) events[date] = [];
    events[date].push(newEvent);

    res.status(201).json(newEvent);
});

app.delete('/api/events/:id', (req, res) => {
    const { id } = req.params;
    let found = false;
    for (const date in events) {
        const initialLength = events[date].length;
        events[date] = events[date].filter(e => e.id !== id);
        if (events[date].length !== initialLength) {
            found = true;
            if (events[date].length === 0) {
                delete events[date];
            }
            break;
        }
    }
    if (found) {
        res.json({ success: true, message: 'Événement supprimé' });
    } else {
        res.status(404).json({ error: 'Événement non trouvé' });
    }
});

app.listen(PORT, () => {
    console.log(`✅ API calendrier démarrée sur http://localhost:${PORT}`);
});
