FROM python:3.9-slim

WORKDIR /app

# Copy application files
COPY . /app

# Expose server port
EXPOSE 8000

# Environment variables
ENV PYTHONUNBUFFERED=1
ENV PORT=8000

# Start server
CMD ["python3", "run.py"]
