import re

class MotivationAnalyzer:
    """
    Analyzes emotional state and energy levels to determine student motivation.
    """
    
    POSITIVE_WORDS = ['content', 'heureux', 'motivé', 'prêt', 'bien', 'excité', 'super', 'génial', 'forme']
    NEGATIVE_WORDS = ['fatigué', 'stressé', 'anxieux', 'nul', 'triste', 'mauvais', 'difficile', 'épuisé', 'flemme']

    def __init__(self):
        pass

    def _analyze_sentiment(self, text):
        """Simple keyword-based sentiment analysis."""
        if not text:
            return 0
        
        text = text.lower()
        score = 0
        for word in self.POSITIVE_WORDS:
            if word in text:
                score += 1
        for word in self.NEGATIVE_WORDS:
            if word in text:
                score -= 1
        
        return score

    def calculate_motivation(self, energy, stress, sleep_quality, mood_text):
        """
        Calculates a motivation score (0-100) and returns a level.
        
        Inputs:
        - energy: 1-10
        - stress: 1-10 (lower is better for motivation usually, but some stress can be drive)
        - sleep_quality: 1-10
        - mood_text: String
        """
        
        sentiment_score = self._analyze_sentiment(mood_text)
        
        # Weighted scoring
        # Energy and sleep are high indicators
        # Stress is tricky: high stress often lowers motivation, 
        # but we treat "moderate" stress as neutral or slightly positive for drive.
        
        base_score = (energy * 4) + (sleep_quality * 3) + (max(0, 10 - stress) * 2)
        
        # Adjust by sentiment
        adjustment = sentiment_score * 5
        final_score = min(100, max(0, base_score + adjustment))
        
        if final_score < 40:
            level = "Faible"
        elif final_score < 75:
            level = "Moyen"
        else:
            level = "Élevé"
            
        return {
            "score": final_score,
            "level": level,
            "sentiment_adjustment": adjustment
        }
