import os
from fastapi import FastAPI
from api.routes import router

app = FastAPI(
    title="AI-Powered Recommendation System",
    description="A clean architecture recommendation API using real Jobs/Courses APIs and Open Router AI for scoring.",
    version="2.0.0"
)

app.include_router(router)

if __name__ == "__main__":
    import uvicorn
    uvicorn.run("main:app", host="0.0.0.0", port=8002, reload=True)
