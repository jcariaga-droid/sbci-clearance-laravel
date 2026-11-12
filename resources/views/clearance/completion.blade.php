
@extends('layouts.app')

@section('page-title', 'Clearance Completion Summary')

@section('content')
@php
    $referenceNumber = 'SBCI-CLR-' . strtoupper(substr(sha1($user->email . $user->created_at), 0, 10));
@endphp

<style>
    .completion-page {
        max-width: 900px;
        margin: 0 auto;
    }

    .completion-actions {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 18px;
    }

    .save-pdf-button {
        background: #203f91;
        color: #fff;
        border: 0;
        border-radius: 7px;
        padding: 12px 20px;
        font-weight: 600;
        cursor: pointer;
    }

    .summary-document {
        background: #fff;
        border: 1px solid #dce2ee;
        border-radius: 12px;
        padding: 42px;
        box-shadow: 0 4px 18px rgba(0,0,0,.06);
    }

    .school-heading {
        text-align: center;
        border-bottom: 3px solid #203f91;
        padding-bottom: 22px;
    }

    .school-heading h2 {
        color: #203f91;
        font-size: 23px;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .school-heading p {
        color: #555;
        margin: 3px 0;
        font-size: 13px;
    }

    .summary-title {
        text-align: center;
        margin: 28px 0;
    }

    .summary-title h1 {
        color: #203f91;
        font-size: 25px;
        font-weight: 800;
        letter-spacing: 1px;
        margin: 0 0 8px;
    }

    .summary-title p {
        color: #64748b;
        font-size: 13px;
        margin: 0;
    }

    .completion-status {
        display: inline-block;
        background: #e8f7ee;
        color: #15803d;
        padding: 7px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        margin-top: 14px;
    }

    .student-details {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin: 28px 0;
        padding: 20px;
        background: #f5f7fb;
        border-radius: 8px;
    }

    .detail-label {
        display: block;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        margin-bottom: 5px;
    }

    .detail-value {
        color: #172554;
        font-size: 14px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .department-heading {
        color: #203f91;
        font-size: 16px;
        font-weight: 800;
        margin: 28px 0 12px;
    }

    .department-list {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .department-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
        color: #334155;
    }

    .department-row:last-child {
        border-bottom: 0;
    }

    .approved-label {
        color: #15803d;
        background: #e8f7ee;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .reference-section {
        margin-top: 28px;
        padding: 18px;
        background: #f5f7fb;
        border-left: 4px solid #203f91;
        border-radius: 6px;
    }

    .reference-section p {
        margin: 5px 0;
        color: #475569;
        font-size: 12px;
    }

    .reference-number {
        color: #203f91;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: .5px;
        overflow-wrap: anywhere;
    }

    .document-note {
        margin-top: 25px;
        padding-top: 16px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
        color: #64748b;
        font-size: 11px;
        line-height: 1.6;
    }

    @media (max-width: 600px) {
        .summary-document {
            padding: 22px 16px;
        }

        .student-details {
            grid-template-columns: 1fr;
        }

        .school-heading h2 {
            font-size: 19px;
        }

        .summary-title h1 {
            font-size: 21px;
        }
    }

    /* PDF / SAVE AS PDF */
    @media print {
        @page {
            size: A4;
            margin: 12mm;
        }

        /* Hide everything by default */
        body * {
            visibility: hidden !important;
        }

        /* Show only the clearance document */
        .summary-document,
        .summary-document * {
            visibility: visible !important;
        }

        .summary-document {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 20px !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .completion-actions {
            display: none !important;
        }

        .completion-page {
            max-width: 100% !important;
            margin: 0 !important;
        }

        .department-row,
        .student-details,
        .reference-section,
        .approved-label,
        .completion-status {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="completion-page">

    <div class="completion-actions">
        <button
            type="button"
            class="save-pdf-button"
            onclick="window.print()"
        >
            Save as PDF
        </button>
    </div>

    <div class="summary-document">

        <div class="school-heading">
            <h2>SUBIC BAY COLLEGES (INC).</h2>
            <p>Olongapo City, Zambales</p>
            <p>STUDENT CLEARANCE MANAGEMENT SYSTEM</p>
        </div>

        <div class="summary-title">
            <h1>CLEARANCE COMPLETED</h1>
            <p>Student Clearance Completion Summary</p>
            <span class="completion-status">ALL DEPARTMENTS APPROVED</span>
        </div>

        <div class="student-details">
            <div>
                <span class="detail-label">Student Name</span>
                <span class="detail-value">{{ $user->name }}</span>
            </div>

            <div>
                <span class="detail-label">Student ID</span>
                <span class="detail-value">{{ $user->student_id ?: 'Not provided' }}</span>
            </div>

            <div>
                <span class="detail-label">Program</span>
                <span class="detail-value">{{ $user->program ?: 'Not provided' }}</span>
            </div>

            <div>
                <span class="detail-label">Year Level</span>
                <span class="detail-value">{{ $user->year_level ?: 'Not provided' }}</span>
            </div>

            <div>
                <span class="detail-label">Completion Date</span>
                <span class="detail-value">
                    {{ $completionDate ? \Carbon\Carbon::parse($completionDate)->format('F d, Y') : 'Not available' }}
                </span>
            </div>

            <div>
                <span class="detail-label">Overall Status</span>
                <span class="detail-value" style="color:#15803d;">COMPLETED</span>
            </div>
        </div>

        <h3 class="department-heading">CLEARANCE DEPARTMENT SUMMARY</h3>

        <div class="department-list">
            @foreach($departments as $department)
                <div class="department-row">
                    <span>{{ $department }}</span>
                    <span class="approved-label">✓ APPROVED</span>
                </div>
            @endforeach
        </div>

        <div class="reference-section">
            <p>SUMMARY REFERENCE NUMBER</p>
            <div class="reference-number">{{ $referenceNumber }}</div>
            <p>Keep this reference number for your records.</p>
        </div>

        <div class="document-note">
            This document is a system-generated summary of the student's clearance
            status in the SBCI Clearance Management System.
            <br>
            This is not an official school certificate or a replacement for
            documents issued by the school.
        </div>

    </div>
</div>
@endsection
