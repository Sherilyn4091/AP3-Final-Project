#!/usr/bin/env python3
# python_analytics/dashboard_analytics.py
"""Dashboard JSON wrapper for Music Lab Student Risk Analytics."""

from __future__ import annotations

import json

from student_risk_analytics import analyze_students, read_payload


def main() -> None:
    payload = read_payload()
    students = payload.get("students") or []
    result = analyze_students(students)

    dashboard_result = {
        "ok": result["ok"],
        "generated_at": result["generated_at"],
        "technique": result["technique"],
        "algorithm": result["algorithm"],
        "summary": result["summary"],
        "charts": result["charts"],
        "top_high_risk_students": result["top_high_risk_students"],
        "factor_summary": result["factor_summary"],
    }

    print(json.dumps(dashboard_result, ensure_ascii=False))


if __name__ == "__main__":
    main()
