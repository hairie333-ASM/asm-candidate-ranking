#!/usr/bin/env python3
"""
Academy of Sciences Malaysia (ASM)
Candidate Ranking & Due Diligence System
Main Application Entry Point
"""

import sys
import os

# Add project root to sys.path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

from app.database import init_db
from app.seed import seed_database
from app.server import run_server

def main():
    port = 8000
    if len(sys.argv) > 1:
        try:
            port = int(sys.argv[1])
        except ValueError:
            print(f"Invalid port: {sys.argv[1]}. Using default 8000.")

    print("=" * 70)
    print("ACADEMY OF SCIENCES MALAYSIA (ASM)")
    print("Candidate Ranking & Due Diligence System")
    print("=" * 70)
    print("1. Initializing database and verifying seed data...")
    seed_database()
    print("2. Starting secure multi-threaded web server...")
    print(f"-> Access the application at: http://127.0.0.1:{port}/")
    print("\nPre-configured Demo Accounts:")
    print("  • Administrator : admin@asm.org.my  / Admin123!")
    print("  • Voting User 1 : voter1@asm.org.my / Voter123!")
    print("  • Voting User 2 : voter2@asm.org.my / Voter123!")
    print("  • Voting User 3 : voter3@asm.org.my / Voter123!")
    print("  • Reviewer      : reviewer@asm.org.my / Reviewer123!")
    print("=" * 70)

    run_server(port)

if __name__ == "__main__":
    main()
