import requests
import json
from typing import List, Dict, Any
from core.config import settings
from models.schemas import UserProfile, ScoredItem, RecommendationResponse

class AIService:
    def __init__(self):
        self.api_url = "https://openrouter.ai/api/v1/chat/completions"
        self.headers = {
            "Authorization": f"Bearer {settings.openrouter_api_key}",
            "Content-Type": "application/json"
        }

    def score_items(self, profile: UserProfile, jobs: List[Dict[str, Any]], courses: List[Dict[str, Any]]) -> RecommendationResponse:
        prompt = self._build_prompt(profile, jobs, courses)
        
        payload = {
            "model": settings.openrouter_model,
            "messages": [
                {
                    "role": "system", 
                    "content": "You are a JSON-only API. You output ONLY valid JSON without any markdown formatting wrappers or explanations. See the schema requested by the user and conform strictly."
                },
                {"role": "user", "content": prompt}
            ],
            "response_format": {"type": "json_object"}
        }

        try:
            response = requests.post(self.api_url, headers=self.headers, json=payload, timeout=45)
            response.raise_for_status()
            
            data = response.json()
            content = data['choices'][0]['message']['content']
            
            if content.startswith("```json"):
                content = content.replace("```json", "", 1).replace("```", "")
            if content.startswith("```"):
                content = content.replace("```", "")
                
            parsed_json = json.loads(content.strip())
            return self._parse_ai_response(parsed_json, jobs, courses)
            
        except requests.exceptions.RequestException as e:
            print(f"OpenRouter Request Error: {e}")
            return self._generate_fallback_response(jobs, courses)
        except json.JSONDecodeError as e:
            print(f"JSON Parsing Error: {e}")
            return self._generate_fallback_response(jobs, courses)
            
    def _build_prompt(self, profile: UserProfile, jobs: List[Dict[str, Any]], courses: List[Dict[str, Any]]) -> str:
        skills_str = ", ".join(profile.skills) if profile.skills else "None"
        job_title = profile.jobTitle or "None"
        edu_level = profile.educationLevel or "None"
        
        job_context = json.dumps(jobs)
        course_context = json.dumps(courses)
        
        return f"""
You are an expert AI career and education advisor.
Analyze the following user profile against the provided lists of real jobs and real courses.

User Profile:
- Skills: {skills_str}
- Job Title: {job_title}
- Education Level: {edu_level}

Jobs available: {job_context}
Courses available: {course_context}

Return a single JSON object with the following structure exactly (populate arrays matching the provided jobs and courses respectively, do NOT generate new or fake jobs/courses):
{{
  "jobs": [
    {{
      "title": "<title of job from input>",
      "company_or_platform": "<company name from input>",
      "link": "<link from input>",
      "compatibility_score": <int 0-100 based on skills gap>,
      "strengths_match": ["<matching skill 1>", "<matching attribute>"],
      "skills_gap": ["<missing skill>"],
      "personalized_summary": "<1 paragraph explaining why this job fits the user>"
    }}
  ],
  "courses": [
    {{
      "title": "<title of course from input>",
      "company_or_platform": "<platform name from input>",
      "link": "<link from input>",
      "compatibility_score": <int 0-100>,
      "strengths_match": ["<reason it fits>"],
      "skills_gap": [],
      "personalized_summary": "<1 paragraph explaining why it's recommended>"
    }}
  ],
  "general_summary": "<2-sentence encouraging summary of their prospects>"
}}
"""

    def _parse_ai_response(self, raw_data: Dict[str, Any], raw_jobs: List[Dict[str, Any]], raw_courses: List[Dict[str, Any]]) -> RecommendationResponse:
        # Map unstructured dict into strictly validated Pydantic models
        jobs_list = []
        for j in raw_data.get("jobs", []):
            jobs_list.append(ScoredItem(
                title=j.get("title", "Unknown Title"),
                company_or_platform=j.get("company_or_platform", "External Company"),
                link=j.get("link", "#"),
                compatibility_score=j.get("compatibility_score", 50),
                strengths_match=j.get("strengths_match", []),
                skills_gap=j.get("skills_gap", []),
                personalized_summary=j.get("personalized_summary", "A great potential fit.")
            ))
            
        courses_list = []
        for c in raw_data.get("courses", []):
            courses_list.append(ScoredItem(
                title=c.get("title", "Unknown Course"),
                company_or_platform=c.get("company_or_platform", "External Platform"),
                link=c.get("link", "#"),
                compatibility_score=c.get("compatibility_score", 50),
                strengths_match=c.get("strengths_match", []),
                skills_gap=c.get("skills_gap", []),
                personalized_summary=c.get("personalized_summary", "A great course for your career.")
            ))
            
        # Ensure we don't return an empty list if AI hallucinates names away, fallback
        if not jobs_list and raw_jobs:
            jobs_list = self._generate_fallback_response(raw_jobs, raw_courses).jobs
        if not courses_list and raw_courses:
            courses_list = self._generate_fallback_response(raw_jobs, raw_courses).courses
            
        return RecommendationResponse(
            jobs=jobs_list,
            courses=courses_list,
            general_summary=raw_data.get("general_summary", "Here are your personalized recommendations based on our analysis.")
        )

    def _generate_fallback_response(self, jobs: List[Dict[str, Any]], courses: List[Dict[str, Any]]) -> RecommendationResponse:
        fallback_jobs = []
        for j in jobs:
            fallback_jobs.append(ScoredItem(
                title=j.get("title", "Job Title"),
                company_or_platform=j.get("company", "Company"),
                link=j.get("link", "#"),
                compatibility_score=75,
                strengths_match=["General Fit"],
                skills_gap=["Specifics Unknown"],
                personalized_summary="This position matches your general profile trajectory."
            ))
            
        fallback_courses = []
        for c in courses:
            fallback_courses.append(ScoredItem(
                title=c.get("title", "Course Title"),
                company_or_platform=c.get("platform", "Platform"),
                link=c.get("link", "#"),
                compatibility_score=80,
                strengths_match=["Career Growth"],
                skills_gap=[],
                personalized_summary="This course has been selected to help advance your career."
            ))
            
        return RecommendationResponse(
            jobs=fallback_jobs,
            courses=fallback_courses,
            general_summary="We found some excellent opportunities for you, despite high AI load."
        )
