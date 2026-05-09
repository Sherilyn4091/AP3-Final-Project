#!/usr/bin/env python3
"""
Music Lab Student Risk Analytics
--------------------------------
Technique: Classification
Algorithm: Interpretable Decision Tree

This script receives student feature rows as JSON through STDIN and returns
student retention risk analytics as JSON through STDOUT.

Why a rule-based decision tree?
- The current Music Lab database has operational records but not yet a clean
  historical target label such as "renewed" vs "stopped" for supervised training.
- This implementation is still a Decision Tree Classification technique because
  each student is classified through transparent decision nodes.
- When enough historical labeled data exists, the decision rules can be replaced
  with a trained scikit-learn DecisionTreeClassifier without changing Laravel UI.
"""

from __future__ import annotations

import json
import math
import sys
from collections import Counter, defaultdict
from datetime import datetime, timezone
from typing import Any, Dict, Iterable, List, Tuple


LOW_RISK = "Low Risk"
MEDIUM_RISK = "Medium Risk"
HIGH_RISK = "High Risk"

PAYMENT_RISK_STATUSES = {
    "pending",
    "partial",
    "partially_paid",
    "unpaid",
    "overdue",
    "failed",
}

INACTIVE_ENROLLMENT_STATUSES = {
    "cancelled",
    "canceled",
    "withdrawn",
    "inactive",
    "dropped",
}

NO_ENROLLMENT_STATUSES = {
    "",
    "none",
    "no_enrollment",
    "not_enrolled",
    "unassigned",
}


# ---------------------------------------------------------------------------
# JSON and number helpers
# ---------------------------------------------------------------------------

def read_payload() -> Dict[str, Any]:
    """Read JSON from STDIN and return a safe dictionary."""
    raw_input = sys.stdin.read().strip()

    if not raw_input:
        return {"students": []}

    try:
        payload = json.loads(raw_input)
    except json.JSONDecodeError as exc:
        return {
            "students": [],
            "_error": f"Invalid JSON received by Python analytics engine: {exc}",
        }

    return payload if isinstance(payload, dict) else {"students": []}


def safe_float(value: Any, default: float = 0.0) -> float:
    """Convert a value to float without throwing errors."""
    if value is None or value == "":
        return default

    try:
        number = float(value)
        if math.isnan(number) or math.isinf(number):
            return default
        return number
    except (TypeError, ValueError):
        return default


def safe_int(value: Any, default: int = 0) -> int:
    """Convert a value to int without throwing errors."""
    return int(round(safe_float(value, float(default))))


def normalize_text(value: Any) -> str:
    """Normalize text values for rule comparison."""
    return str(value or "").strip().lower().replace(" ", "_").replace("-", "_")


def clamp(value: float, minimum: float = 0.0, maximum: float = 100.0) -> float:
    """Limit a numeric score inside an expected range."""
    return max(minimum, min(maximum, value))


# ---------------------------------------------------------------------------
# Decision Tree Classification
# ---------------------------------------------------------------------------

def classify_student(student: Dict[str, Any]) -> Dict[str, Any]:
    """
    Classify one student using interpretable decision-tree rules.

    Output fields:
    - risk_level: Low Risk, Medium Risk, High Risk
    - risk_score: numeric 0-100 score for sorting
    - risk_reasons: specific risk causes
    - recommended_action: admin-friendly intervention
    """
    features = extract_features(student)
    reasons: List[str] = []
    actions: List[str] = []

    risk_level, rule_reasons = apply_decision_tree(features)
    reasons.extend(rule_reasons)

    risk_score = calculate_risk_score(features, risk_level)
    actions.extend(build_recommended_actions(features, risk_level))

    if not reasons:
        reasons.append("Student record shows stable attendance, progress, payment, and lesson activity.")

    return {
        **student,
        "attendance_rate": round(features["attendance_rate"], 2),
        "average_progress_rating": round(features["average_progress_rating"], 2),
        "risk_level": risk_level,
        "risk_score": round(risk_score, 2),
        "risk_reasons": reasons,
        "primary_reason": reasons[0] if reasons else "No major risk detected.",
        "recommended_action": actions[0] if actions else "Continue regular monitoring.",
    }


def extract_features(student: Dict[str, Any]) -> Dict[str, Any]:
    """Map raw student row data into normalized decision-tree features."""
    return {
        "attendance_rate": safe_float(student.get("attendance_rate"), 100.0),
        "absence_count": safe_int(student.get("absence_count"), 0),
        "late_count": safe_int(student.get("late_count"), 0),
        "average_progress_rating": safe_float(student.get("average_progress_rating"), 8.0),
        "payment_status": normalize_text(student.get("payment_status")),
        "enrollment_status": normalize_text(student.get("enrollment_status")),
        "remaining_sessions": safe_int(student.get("remaining_sessions"), 0),
        "completed_sessions": safe_int(student.get("completed_sessions"), 0),
        "days_since_last_lesson": safe_int(student.get("days_since_last_lesson"), 0),
        "is_active": bool(student.get("is_active", True)),
    }


def apply_decision_tree(features: Dict[str, Any]) -> Tuple[str, List[str]]:
    """
    Decision tree rules for retention risk classification.

    The order matters. Clear administrative states are checked first, then
    stronger high-risk warning signs, then medium-risk monitoring signs.
    """
    attendance_rate = features["attendance_rate"]
    absence_count = features["absence_count"]
    late_count = features["late_count"]
    progress_rating = features["average_progress_rating"]
    payment_status = features["payment_status"]
    enrollment_status = features["enrollment_status"]
    remaining_sessions = features["remaining_sessions"]
    days_since_last_lesson = features["days_since_last_lesson"]
    is_active = features["is_active"]

    # Node 1: No enrollment is a monitoring issue, not automatic high risk.
    # This can happen for newly created student records that are not enrolled yet.
    if enrollment_status in NO_ENROLLMENT_STATUSES:
        return MEDIUM_RISK, ["Student has no active enrollment on record."]

    # Node 2: Already inactive or withdrawn students are automatically high risk.
    if not is_active or enrollment_status in INACTIVE_ENROLLMENT_STATUSES:
        return HIGH_RISK, ["Student is inactive, withdrawn, cancelled, or dropped from enrollment."]

    # Node 3: Very weak attendance is the strongest early warning sign.
    if attendance_rate < 60:
        return HIGH_RISK, ["Attendance rate is below 60%, which indicates serious retention risk."]

    # Node 4: Long gap without lesson while sessions remain means intervention is needed.
    if days_since_last_lesson >= 21 and remaining_sessions > 0:
        return HIGH_RISK, ["Student has remaining sessions but no recent lesson for 21 days or more."]

    # Node 5: Payment issue combined with weak attendance is high risk.
    if payment_status in PAYMENT_RISK_STATUSES and attendance_rate < 75:
        return HIGH_RISK, ["Payment status needs attention and attendance is below 75%."]

    # Node 6: Low progress and repeated absences suggest learning disengagement.
    if progress_rating > 0 and progress_rating < 5.5 and absence_count >= 2:
        return HIGH_RISK, ["Progress rating is low and the student has repeated absences."]

    # Node 7: Moderate concerns create medium risk.
    medium_reasons: List[str] = []

    if attendance_rate < 80:
        medium_reasons.append("Attendance rate is below the preferred 80% level.")

    if payment_status in PAYMENT_RISK_STATUSES:
        medium_reasons.append("Payment status may affect lesson continuity.")

    if 14 <= days_since_last_lesson < 21 and remaining_sessions > 0:
        medium_reasons.append("Student has not had a recent lesson for at least 14 days.")

    if 0 < progress_rating < 7:
        medium_reasons.append("Average progress rating is below 7 out of 10.")

    if late_count >= 3:
        medium_reasons.append("Student has multiple late attendance records.")

    if medium_reasons:
        return MEDIUM_RISK, medium_reasons

    return LOW_RISK, []


def calculate_risk_score(features: Dict[str, Any], risk_level: str) -> float:
    """Calculate a sortable numeric risk score from 0 to 100."""
    score = 0.0

    score += max(0.0, 100.0 - features["attendance_rate"]) * 0.35
    score += min(features["absence_count"], 10) * 4.0
    score += min(features["late_count"], 10) * 1.5

    progress = features["average_progress_rating"]
    if progress > 0:
        score += max(0.0, 10.0 - progress) * 3.0

    if features["payment_status"] in PAYMENT_RISK_STATUSES:
        score += 14.0

    if features["enrollment_status"] in NO_ENROLLMENT_STATUSES:
        score += 12.0

    if features["enrollment_status"] in INACTIVE_ENROLLMENT_STATUSES or not features["is_active"]:
        score += 25.0

    if features["remaining_sessions"] > 0:
        score += min(features["days_since_last_lesson"], 45) * 0.7

    # Classification-level adjustment keeps scores aligned with final class.
    if risk_level == HIGH_RISK:
        score = max(score, 70.0)
    elif risk_level == MEDIUM_RISK:
        score = max(score, 40.0)
        score = min(score, 69.0)
    else:
        score = min(score, 39.0)

    return clamp(score)


def build_recommended_actions(features: Dict[str, Any], risk_level: str) -> List[str]:
    """Create admin-friendly recommended actions based on risk factors."""
    if features["enrollment_status"] in NO_ENROLLMENT_STATUSES:
        return ["Verify the student enrollment record and assist with completing an enrollment package."]

    if risk_level == HIGH_RISK:
        if features["payment_status"] in PAYMENT_RISK_STATUSES:
            return ["Contact the student or guardian and verify payment or continuation plans."]
        if features["days_since_last_lesson"] >= 21:
            return ["Schedule a follow-up lesson or call the student/guardian within the week."]
        return ["Ask the assigned instructor to follow up and document an intervention note."]

    if risk_level == MEDIUM_RISK:
        if features["attendance_rate"] < 80:
            return ["Monitor the next two lessons and remind the student about attendance consistency."]
        if features["average_progress_rating"] < 7:
            return ["Coordinate with the instructor to adjust practice goals and lesson focus."]
        return ["Continue monitoring and check again after the next lesson schedule."]

    return ["Continue regular monitoring and encourage consistent practice."]


# ---------------------------------------------------------------------------
# Aggregation for dashboard and reports
# ---------------------------------------------------------------------------

def analyze_students(students: Iterable[Dict[str, Any]]) -> Dict[str, Any]:
    """Classify all students and build dashboard/report analytics."""
    classified_students = [classify_student(student) for student in students]
    classified_students.sort(key=lambda row: safe_float(row.get("risk_score")), reverse=True)

    summary = build_summary(classified_students)
    factor_summary = build_factor_summary(classified_students)
    instructor_summary = build_instructor_summary(classified_students)

    return {
        "ok": True,
        "generated_at": datetime.now(timezone.utc).isoformat(),
        "technique": "Classification",
        "algorithm": "Decision Tree Classification",
        "model_note": "Interpretable decision-tree rules are used because historical labeled retention outcomes are not yet available.",
        "summary": summary,
        "charts": {
            "risk_distribution": build_risk_distribution_chart(summary),
            "risk_factors": build_risk_factor_chart(factor_summary),
        },
        "students": classified_students,
        "top_high_risk_students": [s for s in classified_students if s["risk_level"] == HIGH_RISK][:10],
        "factor_summary": factor_summary,
        "instructor_summary": instructor_summary,
        "report_summary": build_report_summary(summary, factor_summary),
    }


def build_summary(students: List[Dict[str, Any]]) -> Dict[str, Any]:
    """Build risk count summary."""
    counts = Counter(student.get("risk_level", LOW_RISK) for student in students)
    total = len(students)

    return {
        "total_students_analyzed": total,
        "low_risk": counts.get(LOW_RISK, 0),
        "medium_risk": counts.get(MEDIUM_RISK, 0),
        "high_risk": counts.get(HIGH_RISK, 0),
        "high_risk_rate": round((counts.get(HIGH_RISK, 0) / total) * 100, 2) if total else 0,
    }


def build_factor_summary(students: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """Count common primary reasons for risk."""
    reason_counter: Counter[str] = Counter()

    for student in students:
        risk_level = student.get("risk_level")
        if risk_level in {MEDIUM_RISK, HIGH_RISK}:
            reason_counter[student.get("primary_reason") or "Other risk factor"] += 1

    return [
        {"factor": factor, "count": count}
        for factor, count in reason_counter.most_common(8)
    ]


def build_instructor_summary(students: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
    """Summarize risk by assigned instructor."""
    grouped: Dict[str, Dict[str, Any]] = defaultdict(lambda: {
        "instructor_name": "Unassigned",
        "total_students": 0,
        "low_risk": 0,
        "medium_risk": 0,
        "high_risk": 0,
    })

    for student in students:
        instructor_name = student.get("instructor_name") or "Unassigned"
        record = grouped[instructor_name]
        record["instructor_name"] = instructor_name
        record["total_students"] += 1

        risk_key = {
            LOW_RISK: "low_risk",
            MEDIUM_RISK: "medium_risk",
            HIGH_RISK: "high_risk",
        }.get(student.get("risk_level"), "low_risk")

        record[risk_key] += 1

    result = list(grouped.values())
    result.sort(key=lambda row: (row["high_risk"], row["medium_risk"], row["total_students"]), reverse=True)
    return result[:10]


def build_risk_distribution_chart(summary: Dict[str, Any]) -> Dict[str, Any]:
    """Prepare chart-ready risk distribution data."""
    return {
        "labels": [LOW_RISK, MEDIUM_RISK, HIGH_RISK],
        "values": [summary["low_risk"], summary["medium_risk"], summary["high_risk"]],
    }


def build_risk_factor_chart(factor_summary: List[Dict[str, Any]]) -> Dict[str, Any]:
    """Prepare chart-ready risk factor data."""
    return {
        "labels": [item["factor"] for item in factor_summary],
        "values": [item["count"] for item in factor_summary],
    }


def build_report_summary(summary: Dict[str, Any], factor_summary: List[Dict[str, Any]]) -> Dict[str, Any]:
    """Build short narrative data for the Reports page."""
    top_factor = factor_summary[0]["factor"] if factor_summary else "No major recurring risk factor detected."

    if summary["high_risk"] > 0:
        recommendation = "Prioritize follow-up for high-risk students and coordinate with instructors."
    elif summary["medium_risk"] > 0:
        recommendation = "Monitor medium-risk students and review attendance/progress after the next lesson cycle."
    else:
        recommendation = "Student retention indicators are currently stable."

    return {
        "main_finding": top_factor,
        "recommendation": recommendation,
    }


# ---------------------------------------------------------------------------
# CLI entry point
# ---------------------------------------------------------------------------

def main() -> None:
    payload = read_payload()

    if payload.get("_error"):
        print(json.dumps({"ok": False, "error": payload["_error"]}))
        return

    students = payload.get("students") or []
    result = analyze_students(students)
    print(json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    main()
