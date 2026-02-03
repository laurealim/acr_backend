<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACR - {{ $acr->reporting_year }} - {{ $acr->name_bangla }}</title>
    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('{{ storage_path("fonts/SolaimanLipi.ttf") }}') format('truetype');
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'SolaimanLipi', 'Noto Sans Bengali', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .section {
            margin-bottom: 15px;
            border: 1px solid #333;
            padding: 8px;
        }

        .section-title {
            background-color: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
            margin: -8px -8px 8px -8px;
            border-bottom: 1px solid #333;
        }

        .row {
            display: flex;
            margin-bottom: 5px;
        }

        .col {
            flex: 1;
        }

        .col-2 {
            flex: 2;
        }

        .label {
            font-weight: bold;
            color: #333;
        }

        .value {
            padding-left: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table th, table td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: left;
            font-size: 10px;
        }

        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .rating-table th {
            text-align: center;
        }

        .rating-table td:nth-child(2),
        .rating-table td:nth-child(3) {
            text-align: center;
            width: 60px;
        }

        .total-row {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .signature-box {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .signature-item {
            text-align: center;
            width: 30%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
        }

        .info-item {
            display: flex;
        }

        .info-label {
            font-weight: bold;
            min-width: 120px;
        }

        .partial-notice {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .grade-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
        }

        .grade-excellent { background-color: #28a745; color: white; }
        .grade-very-good { background-color: #17a2b8; color: white; }
        .grade-good { background-color: #ffc107; color: black; }
        .grade-average { background-color: #fd7e14; color: white; }
        .grade-below { background-color: #dc3545; color: white; }

        @page {
            margin: 15mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</h1>
        <h2>বার্ষিক গোপনীয় অনুবেদন (ACR)</h2>
        <p>ফরম নং ২৯০-ঘ (সংশোধিত ২০২০)</p>
        <p>প্রতিবেদনের বছর: {{ $acr->reporting_year }}</p>
    </div>

    @if($acr->isPartial())
    <div class="partial-notice">
        <strong>আংশিক গোপনীয় অনুবেদন</strong><br>
        কারণ: {{ $acr->partial_acr_reason }}
    </div>
    @endif

    <!-- Basic Information -->
    <div class="section">
        <div class="section-title">প্রাথমিক তথ্য</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">নাম (বাংলা):</span>
                <span>{{ $acr->name_bangla }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">নাম (ইংরেজি):</span>
                <span>{{ $acr->name_english }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">আইডি নম্বর:</span>
                <span>{{ $acr->id_number ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ব্যাচ:</span>
                <span>{{ $acr->batch ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ক্যাডার:</span>
                <span>{{ $acr->cadre ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">এনআইডি:</span>
                <span>{{ $acr->nid_number ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">মন্ত্রণালয়/বিভাগ:</span>
                <span>{{ $acr->ministry_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ACR সময়কাল:</span>
                <span>{{ $acr->acr_period_from?->format('d/m/Y') }} - {{ $acr->acr_period_to?->format('d/m/Y') }}</span>
            </div>
        </div>
    </div>

    <!-- Position Information -->
    <div class="section">
        <div class="section-title">পদবি সংক্রান্ত তথ্য</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">বিবেচ্য সময়ের পদবি:</span>
                <span>{{ $acr->designation_during_period }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">বিবেচ্য সময়ের কর্মস্থল:</span>
                <span>{{ $acr->workplace_during_period }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">বর্তমান পদবি:</span>
                <span>{{ $acr->current_designation ?? $acr->designation_during_period }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">বর্তমান কর্মস্থল:</span>
                <span>{{ $acr->current_workplace ?? $acr->workplace_during_period }}</span>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="section">
        <div class="section-title">ব্যক্তিগত তথ্য</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">পিতার নাম:</span>
                <span>{{ $acr->father_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">মাতার নাম:</span>
                <span>{{ $acr->mother_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">জন্ম তারিখ:</span>
                <span>{{ $acr->date_of_birth?->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">বৈবাহিক অবস্থা:</span>
                <span>{{ $acr->marital_status }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">সন্তান সংখ্যা:</span>
                <span>{{ $acr->number_of_children ?? 0 }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">শিক্ষাগত যোগ্যতা:</span>
                <span>{{ $acr->highest_education }}</span>
            </div>
        </div>
    </div>

    <!-- Health Information -->
    <div class="section">
        <div class="section-title">১ম অংশ: স্বাস্থ্য পরীক্ষা প্রতিবেদন</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">উচ্চতা:</span>
                <span>{{ $acr->health_height ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ওজন:</span>
                <span>{{ $acr->health_weight ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">দৃষ্টিশক্তি:</span>
                <span>{{ $acr->health_eyesight ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">রক্তের গ্রুপ:</span>
                <span>{{ $acr->health_blood_group ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">রক্তচাপ:</span>
                <span>{{ $acr->health_blood_pressure ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">চিকিৎসা শ্রেণি:</span>
                <span>{{ $acr->health_medical_category ?? 'N/A' }}</span>
            </div>
        </div>
        @if($acr->health_weakness)
        <div class="info-item" style="margin-top: 5px;">
            <span class="info-label">স্বাস্থ্যগত দুর্বলতা:</span>
            <span>{{ $acr->health_weakness }}</span>
        </div>
        @endif
    </div>

    <!-- Work Description -->
    <div class="section">
        <div class="section-title">কাজের সংক্ষিপ্ত বিবরণ</div>
        <ol style="margin-left: 20px;">
            @if($acr->work_description_1)<li>{{ $acr->work_description_1 }}</li>@endif
            @if($acr->work_description_2)<li>{{ $acr->work_description_2 }}</li>@endif
            @if($acr->work_description_3)<li>{{ $acr->work_description_3 }}</li>@endif
            @if($acr->work_description_4)<li>{{ $acr->work_description_4 }}</li>@endif
            @if($acr->work_description_5)<li>{{ $acr->work_description_5 }}</li>@endif
        </ol>
    </div>

    <!-- IO/CO Information -->
    <div class="section">
        <div class="section-title">২য় অংশ: অনুবেদনকারী ও প্রতিস্বাক্ষরকারী তথ্য</div>
        <table>
            <tr>
                <th style="width: 20%;"></th>
                <th style="width: 40%;">অনুবেদনকারী (IO)</th>
                <th style="width: 40%;">প্রতিস্বাক্ষরকারী (CO)</th>
            </tr>
            <tr>
                <td><strong>নাম</strong></td>
                <td>{{ $acr->reviewer_name ?? 'N/A' }}</td>
                <td>{{ $acr->countersigner_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>পদবি</strong></td>
                <td>{{ $acr->reviewer_designation ?? 'N/A' }}</td>
                <td>{{ $acr->countersigner_designation ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>কর্মস্থল</strong></td>
                <td>{{ $acr->reviewer_workplace ?? 'N/A' }}</td>
                <td>{{ $acr->countersigner_workplace ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>সময়কাল</strong></td>
                <td>{{ $acr->reviewer_period_from?->format('d/m/Y') }} - {{ $acr->reviewer_period_to?->format('d/m/Y') }}</td>
                <td>{{ $acr->countersigner_period_from?->format('d/m/Y') }} - {{ $acr->countersigner_period_to?->format('d/m/Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Ratings Section (Only shown if IO has reviewed) -->
    @if($acr->total_score)
    <div class="section">
        <div class="section-title">৪র্থ অংশ: মূল্যায়ন (২৫টি মানদণ্ড, স্কেল ১-৪)</div>

        <table class="rating-table">
            <tr>
                <th>মানদণ্ড</th>
                <th>নম্বর</th>
            </tr>
            <!-- Personal Traits -->
            <tr><td colspan="2" style="background-color: #e8e8e8; font-weight: bold;">ব্যক্তিগত বৈশিষ্ট্য</td></tr>
            <tr><td>নৈতিকতা</td><td>{{ $acr->rating_ethics }}</td></tr>
            <tr><td>সততা</td><td>{{ $acr->rating_honesty }}</td></tr>
            <tr><td>শৃঙ্খলাবোধ</td><td>{{ $acr->rating_discipline }}</td></tr>
            <tr><td>বিচার ও মাত্রাজ্ঞান</td><td>{{ $acr->rating_judgment }}</td></tr>
            <tr><td>ব্যক্তিত্ব</td><td>{{ $acr->rating_personality }}</td></tr>
            <tr><td>সহযোগিতার মনোভাব</td><td>{{ $acr->rating_cooperation }}</td></tr>
            <tr><td>সময়ানুবর্তিতা</td><td>{{ $acr->rating_punctuality }}</td></tr>
            <tr><td>নির্ভরযোগ্যতা</td><td>{{ $acr->rating_reliability }}</td></tr>
            <tr><td>দায়িত্ববোধ</td><td>{{ $acr->rating_responsibility }}</td></tr>
            <tr><td>কাজে আগ্রহ ও মনোযোগ</td><td>{{ $acr->rating_work_interest }}</td></tr>
            <tr><td>ঊর্ধ্বতন কর্তৃপক্ষের নির্দেশনা পালনে তৎপরতা</td><td>{{ $acr->rating_following_orders }}</td></tr>
            <tr><td>উদ্যম ও উদ্যোগ</td><td>{{ $acr->rating_initiative }}</td></tr>
            <tr><td>সেবাগ্রহীতার সঙ্গে ব্যবহার</td><td>{{ $acr->rating_client_behavior }}</td></tr>

            <!-- Work Performance -->
            <tr><td colspan="2" style="background-color: #e8e8e8; font-weight: bold;">কার্যসম্পাদন</td></tr>
            <tr><td>পেশাগত জ্ঞান</td><td>{{ $acr->rating_professional_knowledge }}</td></tr>
            <tr><td>কাজের মান</td><td>{{ $acr->rating_work_quality }}</td></tr>
            <tr><td>কর্তব্যনিষ্ঠা</td><td>{{ $acr->rating_dedication }}</td></tr>
            <tr><td>সম্পাদিত কাজের পরিমাণ</td><td>{{ $acr->rating_work_quantity }}</td></tr>
            <tr><td>সিদ্ধান্ত গ্রহণে দক্ষতা</td><td>{{ $acr->rating_decision_making }}</td></tr>
            <tr><td>সিদ্ধান্ত বাস্তবায়নে সামর্থ্য</td><td>{{ $acr->rating_decision_implementation }}</td></tr>
            <tr><td>অধীনস্থদের তদারকি ও পরিচালনায় সামর্থ্য</td><td>{{ $acr->rating_supervision }}</td></tr>
            <tr><td>দলগত কাজে সহযোগিতা ও নেতৃত্ব</td><td>{{ $acr->rating_teamwork_leadership }}</td></tr>
            <tr><td>ই-নথি ও ইন্টারনেট ব্যবহারে আগ্রহ ও দক্ষতা</td><td>{{ $acr->rating_efile_internet }}</td></tr>
            <tr><td>উদ্ভাবনী কাজে আগ্রহ ও সক্ষমতা</td><td>{{ $acr->rating_innovation }}</td></tr>
            <tr><td>প্রকাশ ক্ষমতা (লিখন)</td><td>{{ $acr->rating_written_expression }}</td></tr>
            <tr><td>প্রকাশ ক্ষমতা (বাচনিক)</td><td>{{ $acr->rating_verbal_expression }}</td></tr>

            <tr class="total-row">
                <td><strong>মোট নম্বর</strong></td>
                <td><strong>{{ $acr->total_score }}/100</strong></td>
            </tr>
            <tr class="total-row">
                <td><strong>গ্রেড</strong></td>
                <td>
                    <span class="grade-badge
                        @if($acr->total_score >= 95) grade-excellent
                        @elseif($acr->total_score >= 90) grade-very-good
                        @elseif($acr->total_score >= 80) grade-good
                        @elseif($acr->total_score >= 70) grade-average
                        @else grade-below
                        @endif
                    ">{{ $acr->score_in_words }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- IO Comments -->
    @if($acr->reviewer_additional_comments)
    <div class="section">
        <div class="section-title">৫ম অংশ: অনুবেদনকারীর মন্তব্য</div>
        <p>{{ $acr->reviewer_additional_comments }}</p>
        @if($acr->comment_type)
        <p><strong>মন্তব্যের ধরন:</strong> {{ $acr->comment_type === 'praise' ? 'প্রশংসামূলক' : 'বিরূপ' }}</p>
        @endif
        <p><strong>স্বাক্ষরের তারিখ:</strong> {{ $acr->reviewer_signature_date?->format('d/m/Y') }}</p>
        @if($acr->reviewer_memo_number)
        <p><strong>স্মারক নম্বর:</strong> {{ $acr->reviewer_memo_number }}</p>
        @endif
    </div>
    @endif

    <!-- CO Comments -->
    @if($acr->countersigner_agrees !== null)
    <div class="section">
        <div class="section-title">৬ষ্ঠ অংশ: প্রতিস্বাক্ষরকারীর মন্তব্য</div>
        <p><strong>একমত:</strong> {{ $acr->countersigner_agrees ? 'হ্যাঁ' : 'না' }}</p>
        @if($acr->countersigner_agrees && $acr->countersigner_agree_comment)
        <p><strong>মন্তব্য:</strong> {{ $acr->countersigner_agree_comment }}</p>
        @endif
        @if(!$acr->countersigner_agrees && $acr->countersigner_disagree_comment)
        <p><strong>দ্বিমতের কারণ:</strong> {{ $acr->countersigner_disagree_comment }}</p>
        @endif
        @if($acr->countersigner_score)
        <p><strong>প্রদত্ত নম্বর:</strong> {{ $acr->countersigner_score }}/100 ({{ $acr->countersigner_score_in_words }})</p>
        @endif
        <p><strong>স্বাক্ষরের তারিখ:</strong> {{ $acr->countersigner_signature_date?->format('d/m/Y') }}</p>
    </div>
    @endif

    <!-- Dossier Section -->
    @if($acr->dossier_received_date)
    <div class="section">
        <div class="section-title">৭ম অংশ: ডোসিয়ার সংরক্ষণকারী</div>
        <p><strong>প্রাপ্তির তারিখ:</strong> {{ $acr->dossier_received_date?->format('d/m/Y') }}</p>
        @if($acr->dossier_action_taken)
        <p><strong>গৃহীত পদক্ষেপ:</strong> {{ $acr->dossier_action_taken }}</p>
        @endif
        @if($acr->dossier_average_score)
        <p><strong>গড় নম্বর:</strong> {{ $acr->dossier_average_score }}/100 ({{ $acr->dossier_average_score_in_words }})</p>
        @endif
    </div>
    @endif
    @endif

    <div class="footer">
        <p>এই নথিটি ACR ব্যবস্থাপনা সিস্টেম থেকে স্বয়ংক্রিয়ভাবে তৈরি করা হয়েছে</p>
        <p>তৈরির তারিখ: {{ now()->format('d/m/Y h:i A') }}</p>
        <p>ACR ID: {{ $acr->id }} | Employee ID: {{ $employee->employee_id }}</p>
    </div>
</body>
</html>
