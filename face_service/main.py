from fastapi import FastAPI, Depends, HTTPException, Request, status
from fastapi.middleware.cors import CORSMiddleware
from sqlalchemy.orm import Session
from pydantic import BaseModel, Field
from typing import List
import numpy as np
from scipy.spatial.distance import cosine
from slowapi import Limiter, _rate_limit_exceeded_handler
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded
import logging
import json

from database import SessionLocal, FaceEmbedding, engine, Base

# Create tables if they don't exist (though Symfony migrations will manage this)
Base.metadata.create_all(bind=engine)

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

limiter = Limiter(key_func=get_remote_address)
app = FastAPI(title="Face Recognition Microservice")

app.state.limiter = limiter
app.add_exception_handler(RateLimitExceeded, _rate_limit_exceeded_handler)

# CORS configuration
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:8000", "http://127.0.0.1:8000"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Dependency to get DB session
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

class RegisterRequest(BaseModel):
    user_id: int
    descriptor: List[float] = Field(..., min_length=128, max_length=128)

class LoginRequest(BaseModel):
    descriptor: List[float] = Field(..., min_length=128, max_length=128)

@app.post("/face/register", status_code=status.HTTP_200_OK)
def register_face(request: RegisterRequest, db: Session = Depends(get_db)):
    try:
        existing = db.query(FaceEmbedding).filter(FaceEmbedding.user_id == request.user_id).first()
        if existing:
            existing.descriptor = request.descriptor
            logger.info(f"Updated face descriptor for user {request.user_id}")
        else:
            new_embedding = FaceEmbedding(user_id=request.user_id, descriptor=request.descriptor)
            db.add(new_embedding)
            logger.info(f"Created new face descriptor for user {request.user_id}")
            
        db.commit()
        return {"status": "success", "message": "Face registered successfully"}
    except Exception as e:
        db.rollback()
        logger.error(f"Error registering face: {str(e)}")
        raise HTTPException(status_code=500, detail="Internal server error")

@app.post("/face/login")
@limiter.limit("10/minute")
def login_face(request: Request, login_req: LoginRequest, db: Session = Depends(get_db)):
    threshold = 0.5  # Cosine distance threshold (lower is better match)
    input_descriptor = np.array(login_req.descriptor)
    
    embeddings = db.query(FaceEmbedding).all()
    if not embeddings:
        raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="No faces registered")

    best_match = None
    best_distance = float('inf')

    for embedding in embeddings:
        # Descriptor parsing - checking if stored as string or array based on db format
        try:
            stored_desc = np.array(embedding.descriptor)
        except:
            stored_desc = np.array(json.loads(embedding.descriptor))
            
        dist = cosine(input_descriptor, stored_desc)
        
        if dist < best_distance:
            best_distance = dist
            best_match = embedding.user_id

    if best_match is not None and best_distance < threshold:
        # Calculate confidence %: if distance is 0 -> 100%, if distance is 0.5 -> 0%
        confidence = max(0, min(100, (1 - (best_distance / threshold)) * 100))
        return {"user_id": best_match, "confidence": round(confidence, 2)}
    
    raise HTTPException(status_code=status.HTTP_401_UNAUTHORIZED, detail="Face not recognized or below threshold")
