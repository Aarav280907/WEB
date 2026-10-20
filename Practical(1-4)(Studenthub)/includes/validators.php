<?php
/**
 * StudentHub Portal - Server-side validation for student and event records.
 * Used by the normal forms (Practical 11, 12) and by the JSON API (Practical 13),
 * so the same rules apply no matter how the data arrives.
 *
 * Each function returns [cleanData, errors]. $errors is an array: field => message.
 */

const COURSES   = ['Computer Engineering', 'Information Technology', 'AI & Data Science',
                   'Electronics & Communication', 'Mechanical Engineering', 'Civil Engineering'];
const GENDERS   = ['Male', 'Female', 'Other'];
const CATEGORIES = ['Tech Fest', 'Examination', 'Workshop', 'Career', 'Academic', 'Sports', 'Cultural', 'Social'];

function validate_student(array $in): array
{
    $d = [
        'student_id' => strtoupper(trim((string)($in['student_id'] ?? ''))),
        'fullname'   => trim(strip_tags((string)($in['fullname'] ?? ''))),
        'email'      => strtolower(trim((string)($in['email'] ?? ''))),
        'mobile'     => trim((string)($in['mobile'] ?? '')),
        'course'     => (string)($in['course'] ?? ''),
        'semester'   => (string)($in['semester'] ?? ''),
        'gender'     => (string)($in['gender'] ?? ''),
        'sgpa'       => trim((string)($in['sgpa'] ?? '')),
        'attendance' => trim((string)($in['attendance'] ?? '')),
        'status'     => (string)($in['status'] ?? 'active'),
    ];
    $err = [];

    if (!preg_match('/^[A-Z0-9]{5,15}$/', $d['student_id'])) $err['student_id'] = 'Student ID must be 5-15 letters/digits (e.g. 25DCE072).';
    if (!preg_match("/^[A-Za-z][A-Za-z .'-]{2,59}$/", $d['fullname'])) $err['fullname'] = 'Name must be 3-60 letters (spaces, dots, hyphens allowed).';
    if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || strlen($d['email']) > 100) $err['email'] = 'Enter a valid e-mail address.';
    if (!preg_match('/^[0-9]{10}$/', $d['mobile'])) $err['mobile'] = 'Mobile number must be exactly 10 digits.';
    if (!in_array($d['course'], COURSES, true)) $err['course'] = 'Select a valid course.';
    if (!ctype_digit($d['semester']) || (int)$d['semester'] < 1 || (int)$d['semester'] > 8) $err['semester'] = 'Semester must be between 1 and 8.';
    if (!in_array($d['gender'], GENDERS, true)) $err['gender'] = 'Select a gender.';
    if (!is_numeric($d['sgpa']) || (float)$d['sgpa'] < 0 || (float)$d['sgpa'] > 10) $err['sgpa'] = 'SGPA must be a number between 0 and 10.';
    if (!ctype_digit($d['attendance']) || (int)$d['attendance'] > 100) $err['attendance'] = 'Attendance must be a whole number between 0 and 100.';
    if (!in_array($d['status'], ['active', 'inactive'], true)) $err['status'] = 'Status must be active or inactive.';

    if (!$err) {                                   // convert to the final types
        $d['semester']   = (int)$d['semester'];
        $d['sgpa']       = round((float)$d['sgpa'], 2);
        $d['attendance'] = (int)$d['attendance'];
    }
    return [$d, $err];
}

function validate_event(array $in): array
{
    $d = [
        'title'       => trim(strip_tags((string)($in['title'] ?? ''))),
        'category'    => (string)($in['category'] ?? ''),
        'event_date'  => trim((string)($in['event_date'] ?? '')),
        'event_time'  => trim(strip_tags((string)($in['event_time'] ?? ''))),
        'venue'       => trim(strip_tags((string)($in['venue'] ?? ''))),
        'organizer'   => trim(strip_tags((string)($in['organizer'] ?? ''))),
        'description' => trim(strip_tags((string)($in['description'] ?? ''))),
        'status'      => (string)($in['status'] ?? 'open'),
    ];
    $err = [];

    if (mb_strlen($d['title']) < 5 || mb_strlen($d['title']) > 150) $err['title'] = 'Title must be 5-150 characters.';
    if (!in_array($d['category'], CATEGORIES, true)) $err['category'] = 'Select a valid category.';
    $dt = DateTime::createFromFormat('Y-m-d', $d['event_date']);
    if (!$dt || $dt->format('Y-m-d') !== $d['event_date']) $err['event_date'] = 'Enter a valid date (YYYY-MM-DD).';
    if (mb_strlen($d['event_time']) > 30) $err['event_time'] = 'Time text is too long (max 30 characters).';
    if (mb_strlen($d['venue']) > 120) $err['venue'] = 'Venue is too long (max 120 characters).';
    if (mb_strlen($d['organizer']) > 120) $err['organizer'] = 'Organizer is too long (max 120 characters).';
    if (mb_strlen($d['description']) > 2000) $err['description'] = 'Description is too long (max 2000 characters).';
    if (!in_array($d['status'], ['open', 'closed'], true)) $err['status'] = 'Status must be open or closed.';

    return [$d, $err];
}
