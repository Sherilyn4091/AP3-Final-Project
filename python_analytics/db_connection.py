#!/usr/bin/env python3
# python_analytics/db_connection.py
"""
Python database connection placeholder.

The production integration does not open a direct Python database connection.
Laravel remains responsible for database access and sends sanitized student feature
rows to Python through STDIN. This file is kept to document the boundary clearly
and avoid duplicated credentials.
"""

from __future__ import annotations


def explain_connection_strategy() -> str:
    return (
        "Laravel handles Supabase/PostgreSQL queries. "
        "Python receives prepared analytics rows through STDIN and returns JSON."
    )
