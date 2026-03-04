from pydantic import BaseModel, Field
from typing import List, Optional

class UserProfile(BaseModel):
    skills: List[str] = Field(default_factory=list)
    educationLevel: Optional[str] = None
    jobTitle: Optional[str] = None
    targetJob: Optional[str] = None

class RoadmapStep(BaseModel):
    step_number: int
    title: str
    type: str # 'Course', 'Project', 'Action'
    duration_weeks: int
    description: str

class RecommendationRequest(BaseModel):
    profile: UserProfile

class ScoredItem(BaseModel):
    title: str
    company_or_platform: str
    link: str
    compatibility_score: int
    strengths_match: List[str] = Field(default_factory=list)
    skills_gap: List[str] = Field(default_factory=list)
    personalized_summary: str

class RecommendationResponse(BaseModel):
    jobs: List[ScoredItem]
    courses: List[ScoredItem]
    general_summary: str
    roadmap: List[RoadmapStep] = Field(default_factory=list)
