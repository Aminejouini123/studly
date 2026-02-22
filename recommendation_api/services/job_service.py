import requests
from typing import List, Dict, Any

class JobService:
    def __init__(self):
        self.remotive_url = "https://remotive.com/api/remote-jobs"
        
    def fetch_jobs(self, skills: List[str], job_title: str, limit: int = 3) -> List[Dict[str, Any]]:
        raw_skills = " ".join(skills) if skills else (job_title or "developer")
        skill_words = [word for word in raw_skills.split() if len(word) > 2]
        search_term = skill_words[0] if skill_words else (job_title or "developer")
        
        try:
            params = {
                "search": search_term,
                "limit": limit
            }
            response = requests.get(self.remotive_url, params=params, timeout=10)
            response.raise_for_status()
            
            data = response.json()
            jobs = data.get("jobs", [])
            
            # If no jobs found with the specific skill, try job title
            if not jobs and job_title and job_title != search_term:
                params["search"] = job_title
                response = requests.get(self.remotive_url, params=params, timeout=10)
                jobs = response.json().get("jobs", [])
                
            # If still no jobs, fallback to "developer" just to get SOME real jobs
            if not jobs:
                params["search"] = "developer"
                response = requests.get(self.remotive_url, params=params, timeout=10)
                jobs = response.json().get("jobs", [])
            
            formatted_jobs = []
            for j in jobs[:limit]:
                formatted_jobs.append({
                    "title": j.get("title", ""),
                    "company": j.get("company_name", ""),
                    "link": j.get("url", ""),
                    "description": j.get("description", "")[:500] # truncate for AI context
                })
                
            return formatted_jobs
            
        except requests.exceptions.RequestException as e:
            print(f"Error fetching jobs from Remotive: {e}")
            return []
