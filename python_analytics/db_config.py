#!/usr/bin/env python3
# python_analytics/db_config.py
"""
Database configuration helper.

Current integration pattern:
- Laravel already connects to Supabase/PostgreSQL using the existing .env file.
- Laravel queries the needed student data and sends it to Python through STDIN.
- Python returns JSON analytics to Laravel.

This avoids storing database credentials twice and keeps Laravel as the main system.
"""

from __future__ import annotations

import os
from typing import Dict, Optional


def get_database_env() -> Dict[str, Optional[str]]:
    """Return database environment values if direct Python DB access is ever needed."""
    return {
        "DB_CONNECTION": os.getenv("DB_CONNECTION"),
        "DB_HOST": os.getenv("DB_HOST"),
        "DB_PORT": os.getenv("DB_PORT"),
        "DB_DATABASE": os.getenv("DB_DATABASE"),
        "DB_USERNAME": os.getenv("DB_USERNAME"),
        "DB_PASSWORD": os.getenv("DB_PASSWORD"),
    }
