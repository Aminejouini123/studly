class Scheduler:
    """
    Optimizes task planning based on motivation levels and difficulty.
    """
    
    def __init__(self, motivation_level):
        self.motivation_level = motivation_level # "Faible", "Moyen", "Élevé"

    def optimize_tasks(self, tasks):
        """
        Organizes tasks: prioritizes, adjusts duration, and balances.
        
        Expected task structure:
        {
            "id": int,
            "title": str,
            "difficulty": 1-10,
            "initial_duration": int (minutes)
        }
        """
        
        # 1. Sort by difficulty based on motivation
        if self.motivation_level == "Élevé":
            # Eat the frog: hard tasks first
            sorted_tasks = sorted(tasks, key=lambda x: x['difficulty'], reverse=True)
        elif self.motivation_level == "Faible":
            # Easy wins first to build momentum
            sorted_tasks = sorted(tasks, key=lambda x: x['difficulty'])
        else:
            # Balanced: mix of everything
            sorted_tasks = tasks
            
        optimized_schedule = []
        
        for task in sorted_tasks:
            # 2. Adjust duration (Pomodoro adjustments)
            # If motivation is low, shorten sessions to avoid burnout
            # If motivation is high, allow longer focus blocks
            
            initial_duration = task.get('initial_duration', 60)
            
            if self.motivation_level == "Faible":
                pomodoro_block = 25
                short_break = 5
            elif self.motivation_level == "Moyen":
                pomodoro_block = 50
                short_break = 10
            else: # Élevé
                pomodoro_block = 90
                short_break = 15
                
            # Calculate how many blocks are needed
            num_blocks = max(1, round(initial_duration / pomodoro_block))
            
            session = {
                "id": task['id'],
                "title": task['title'],
                "original_difficulty": task['difficulty'],
                "blocks": num_blocks,
                "block_duration": pomodoro_block,
                "break_duration": short_break,
                "total_time": num_blocks * (pomodoro_block + short_break)
            }
            
            optimized_schedule.append(session)
            
        return optimized_schedule
