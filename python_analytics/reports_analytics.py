#!/usr/bin/env python3
# python_analytics/reports_analytics.py

"""Reports JSON wrapper for Music Lab Student Risk Analytics."""

from __future__ import annotations

import json

from student_risk_analytics import analyze_students, read_payload


def main() -> None:
    payload = read_payload()
    students = payload.get("students") or []
    result = analyze_students(students)

    report_result = {
        "ok": result["ok"],
        "generated_at": result["generated_at"],
        "technique": result["technique"],
        "algorithm": result["algorithm"],
        "summary": result["summary"],
        "charts": result["charts"],
        "top_high_risk_students": result["top_high_risk_students"],
        "factor_summary": result["factor_summary"],
        "instructor_summary": result["instructor_summary"],
        "report_summary": result["report_summary"],
    }

    print(json.dumps(report_result, ensure_ascii=False))


if __name__ == "__main__":
    main()
