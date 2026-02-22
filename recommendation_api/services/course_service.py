import requests
from typing import List, Dict, Any
from core.config import settings

class CourseService:
    def __init__(self):
        self.coursera_api_url = "https://api.coursera.org/api/courses.v1"
        self.token_url = "https://accounts.coursera.org/oauth2/v1/token"
        self._access_token = None
        
    def _get_auth_token(self) -> str:
        if settings.coursera_access_token:
            return settings.coursera_access_token
            
        if self._access_token:
            return self._access_token
            
        if not settings.coursera_client_id or not settings.coursera_client_secret:
            return ""
            
        try:
            response = requests.post(
                self.token_url,
                data={"grant_type": "client_credentials"},
                auth=(settings.coursera_client_id, settings.coursera_client_secret),
                timeout=10
            )
            response.raise_for_status()
            data = response.json()
            self._access_token = data.get("access_token", "")
            return self._access_token
        except requests.exceptions.RequestException as e:
            print(f"Failed to fetch Coursera Auth Token: {e}")
            return ""
        
    def fetch_courses(self, skills: List[str], job_title: str, limit: int = 3) -> List[Dict[str, Any]]:
        raw_skills = " ".join(skills) if skills else (job_title or "programming")
        skill_words = [word.lower() for word in raw_skills.split() if len(word) > 2]
        if not skill_words:
            skill_words = ["programming"]
        
        token = self._get_auth_token()
        headers = {}
        if token:
            headers["Authorization"] = f"Bearer {token}"
        else:
            print("Coursera credentials not provided or failed, returning empty courses list.")
            return []
            
        try:
            # We fetch a catalog slice and filter locally since public search is deprecated
            params = {
                "limit": 1000
            }
            
            response = requests.get(self.coursera_api_url, params=params, headers=headers, timeout=15)
            response.raise_for_status()
            
            data = response.json()
            elements = data.get("elements", [])
            
            matched_courses = []
            for c in elements:
                name = c.get("name", "")
                name_lower = name.lower()
                # Check if any of our skill words are in the course name
                if any(word in name_lower for word in skill_words):
                    matched_courses.append({
                        "title": name,
                        "platform": "Coursera",
                        "link": f"https://www.coursera.org/learn/{c.get('slug', '')}",
                        "description": f"Professional course: {name}"
                    })
                    if len(matched_courses) >= limit:
                        break
                        
            # Fallback if no specific skills matched
            if not matched_courses:
                fallback_term = job_title.lower() if job_title else "programming"
                for c in elements:
                    name = c.get("name", "")
                    name_lower = name.lower()
                    if fallback_term in name_lower or "developer" in name_lower or "software" in name_lower:
                        matched_courses.append({
                            "title": name,
                            "platform": "Coursera",
                            "link": f"https://www.coursera.org/learn/{c.get('slug', '')}",
                            "description": f"Professional course: {name}"
                        })
                        if len(matched_courses) >= limit:
                            break
                            
            return matched_courses
            
        except requests.exceptions.RequestException as e:
            print(f"Error fetching courses from Coursera: {e}")
            return []
