from fastapi import APIRouter
from models.schemas import RecommendationRequest, RecommendationResponse
from services.job_service import JobService
from services.course_service import CourseService
from services.ai_service import AIService

router = APIRouter()
job_service = JobService()
course_service = CourseService()
ai_service = AIService()

@router.post("/recommend", response_model=RecommendationResponse)
def get_recommendations(req: RecommendationRequest):
    profile = req.profile
    
    # 1. Fetch real jobs from Remotive API
    raw_jobs = job_service.fetch_jobs(skills=profile.skills, job_title=profile.jobTitle, limit=3)
    
    # 2. Fetch real courses from Udemy API
    raw_courses = course_service.fetch_courses(skills=profile.skills, job_title=profile.jobTitle, limit=3)
    
    # 3. Score candidates with OpenRouter AI
    scored_results = ai_service.score_items(profile=profile, jobs=raw_jobs, courses=raw_courses)
    
    return scored_results
