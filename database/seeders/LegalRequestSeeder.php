<?php

namespace Database\Seeders;

use App\Models\LegalApproval;
use App\Models\LegalAttachment;
use App\Models\LegalNote;
use App\Models\LegalOutputDocument;
use App\Models\LegalRequest;
use App\Models\LegalTimelineEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegalRequestSeeder extends Seeder
{
    public function run(): void
    {
        $requests = [
            [
                'id' => '1',
                'request_code' => 'LGL-2026-0148',
                'submitted_at' => '2026-08-14',
                'unit' => 'Procurement',
                'category' => 'Contract Review',
                'title' => 'Vendor agreement review — PT Anugrah Logistik',
                'applicant' => 'Rina Kartika',
                'applicant_email' => 'rina.kartika@ssc.co.id',
                'priority' => 'urgent',
                'target_date' => '2026-08-21',
                'current_stage' => 'Legal Review',
                'status' => 'in_process',
                'counterparty' => 'PT Anugrah Logistik',
                'estimated_value' => 'IDR 2.400.000.000',
                'description' => 'Permohonan review draft perjanjian kerja sama jasa logistik untuk periode 2026–2028. Fokus pada klausul penalti keterlambatan, indemnity, dan mekanisme terminasi.',
                'attachments' => [
                    ['id' => 'a1', 'file_name' => 'draft-agreement-v3.pdf', 'file_type' => 'PDF', 'file_size_kb' => 842, 'uploaded_by' => 'Rina Kartika', 'uploaded_at' => '2026-08-14'],
                    ['id' => 'a2', 'file_name' => 'vendor-profile.xlsx', 'file_type' => 'XLSX', 'file_size_kb' => 214, 'uploaded_by' => 'Rina Kartika', 'uploaded_at' => '2026-08-14'],
                    ['id' => 'a3', 'file_name' => 'board-approval-scan.pdf', 'file_type' => 'PDF', 'file_size_kb' => 1180, 'uploaded_by' => 'Dwi Prasetyo', 'uploaded_at' => '2026-08-15'],
                ],
                'timeline' => [
                    ['id' => 't1', 'stage' => 'Submitted', 'actor' => 'Rina Kartika', 'occurred_at' => '2026-08-14 09:12', 'state' => 'done', 'note' => null],
                    ['id' => 't2', 'stage' => 'Unit Head Approval', 'actor' => 'Dwi Prasetyo', 'occurred_at' => '2026-08-15 11:40', 'state' => 'done', 'note' => 'Disetujui, lanjut ke legal.'],
                    ['id' => 't3', 'stage' => 'Legal Review', 'actor' => 'Andini Lestari', 'occurred_at' => '2026-08-17 08:30', 'state' => 'current', 'note' => 'Review klausul penalti sedang berjalan.'],
                    ['id' => 't4', 'stage' => 'Final Approval', 'actor' => 'Legal Manager', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                    ['id' => 't5', 'stage' => 'Completed', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                ],
                'notes' => [
                    ['id' => 'n1', 'author' => 'Andini Lestari', 'created_at_str' => '2026-08-17 10:05', 'body' => 'Klausul 8.2 tentang penalti keterlambatan perlu dibatasi maksimum 5% nilai kontrak.'],
                    ['id' => 'n2', 'author' => 'Andini Lestari', 'created_at_str' => '2026-08-18 14:22', 'body' => 'Menunggu konfirmasi vendor atas revisi klausul indemnity.'],
                ],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap1', 'approver' => 'Dwi Prasetyo', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-08-15 11:40', 'comment' => 'Sesuai kebutuhan operasional.'],
                    ['id' => 'ap2', 'approver' => 'Legal Manager', 'role' => 'Legal Manager', 'decision' => 'pending', 'decided_at' => null, 'comment' => null],
                ],
            ],
            [
                'id' => '2',
                'request_code' => 'LGL-2026-0147',
                'submitted_at' => '2026-08-12',
                'unit' => 'Human Capital',
                'category' => 'Legal Opinion',
                'title' => 'Legal opinion atas skema outsourcing tenaga kerja',
                'applicant' => 'Bayu Nugroho',
                'applicant_email' => 'bayu.nugroho@ssc.co.id',
                'priority' => 'high',
                'target_date' => '2026-08-20',
                'current_stage' => 'Waiting Review',
                'status' => 'waiting_review',
                'counterparty' => null,
                'estimated_value' => null,
                'description' => 'Permintaan legal opinion terkait kepatuhan skema alih daya terhadap regulasi ketenagakerjaan terbaru.',
                'attachments' => [
                    ['id' => 'a4', 'file_name' => 'skema-outsourcing.docx', 'file_type' => 'DOCX', 'file_size_kb' => 96, 'uploaded_by' => 'Bayu Nugroho', 'uploaded_at' => '2026-08-12'],
                ],
                'timeline' => [
                    ['id' => 't6', 'stage' => 'Submitted', 'actor' => 'Bayu Nugroho', 'occurred_at' => '2026-08-12 15:02', 'state' => 'done', 'note' => null],
                    ['id' => 't7', 'stage' => 'Waiting Review', 'actor' => 'Legal Intake', 'occurred_at' => '2026-08-12 16:10', 'state' => 'current', 'note' => null],
                    ['id' => 't8', 'stage' => 'Legal Review', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                    ['id' => 't9', 'stage' => 'Completed', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                ],
                'notes' => [],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap3', 'approver' => 'Sari Wulandari', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-08-12 15:50', 'comment' => null],
                ],
            ],
            [
                'id' => '3',
                'request_code' => 'LGL-2026-0141',
                'submitted_at' => '2026-07-28',
                'unit' => 'Finance',
                'category' => 'Agreement Drafting',
                'title' => 'Drafting perjanjian pinjam-meminjam antar entitas',
                'applicant' => 'Tio Ramadhan',
                'applicant_email' => 'tio.ramadhan@ssc.co.id',
                'priority' => 'medium',
                'target_date' => '2026-08-10',
                'current_stage' => 'Completed',
                'status' => 'completed',
                'counterparty' => null,
                'estimated_value' => 'IDR 15.000.000.000',
                'description' => 'Penyusunan intercompany loan agreement termasuk skema bunga dan jaminan korporasi.',
                'attachments' => [
                    ['id' => 'a5', 'file_name' => 'term-sheet.pdf', 'file_type' => 'PDF', 'file_size_kb' => 320, 'uploaded_by' => 'Tio Ramadhan', 'uploaded_at' => '2026-07-28'],
                    ['id' => 'a6', 'file_name' => 'cashflow-projection.xlsx', 'file_type' => 'XLSX', 'file_size_kb' => 512, 'uploaded_by' => 'Tio Ramadhan', 'uploaded_at' => '2026-07-29'],
                ],
                'timeline' => [
                    ['id' => 't10', 'stage' => 'Submitted', 'actor' => 'Tio Ramadhan', 'occurred_at' => '2026-07-28 08:44', 'state' => 'done', 'note' => null],
                    ['id' => 't11', 'stage' => 'Legal Drafting', 'actor' => 'Andini Lestari', 'occurred_at' => '2026-08-01 09:20', 'state' => 'done', 'note' => null],
                    ['id' => 't12', 'stage' => 'Final Approval', 'actor' => 'Legal Manager', 'occurred_at' => '2026-08-08 13:15', 'state' => 'done', 'note' => null],
                    ['id' => 't13', 'stage' => 'Completed', 'actor' => 'Legal Admin', 'occurred_at' => '2026-08-09 10:00', 'state' => 'done', 'note' => null],
                ],
                'notes' => [
                    ['id' => 'n3', 'author' => 'Andini Lestari', 'created_at_str' => '2026-08-05 09:00', 'body' => "Bunga disepakati mengikuti arm's length principle, 7,5% p.a."],
                ],
                'output_documents' => [
                    ['id' => 'd1', 'doc_number' => 'PKS/LGL/VIII/2026/012', 'title' => 'Intercompany Loan Agreement', 'doc_type' => 'Agreement', 'issued_at' => '2026-08-09'],
                ],
                'approvals' => [
                    ['id' => 'ap4', 'approver' => 'Hendra Wijaya', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-07-29 10:30', 'comment' => null],
                    ['id' => 'ap5', 'approver' => 'Legal Manager', 'role' => 'Legal Manager', 'decision' => 'approved', 'decided_at' => '2026-08-08 13:15', 'comment' => 'Draft final disetujui.'],
                ],
            ],
            [
                'id' => '4',
                'request_code' => 'LGL-2026-0139',
                'submitted_at' => '2026-07-22',
                'unit' => 'IT & Digital',
                'category' => 'Licensing',
                'title' => 'Review SaaS subscription terms — analytics platform',
                'applicant' => 'Fajar Setiawan',
                'applicant_email' => 'fajar.setiawan@ssc.co.id',
                'priority' => 'low',
                'target_date' => '2026-08-15',
                'current_stage' => 'Legal Review',
                'status' => 'in_process',
                'counterparty' => 'Nordwind Analytics BV',
                'estimated_value' => null,
                'description' => 'Review terms of service dan data processing agreement untuk platform analitik berbasis cloud.',
                'attachments' => [
                    ['id' => 'a7', 'file_name' => 'tos-nordwind.pdf', 'file_type' => 'PDF', 'file_size_kb' => 660, 'uploaded_by' => 'Fajar Setiawan', 'uploaded_at' => '2026-07-22'],
                    ['id' => 'a8', 'file_name' => 'dpa-draft.pdf', 'file_type' => 'PDF', 'file_size_kb' => 415, 'uploaded_by' => 'Fajar Setiawan', 'uploaded_at' => '2026-07-22'],
                ],
                'timeline' => [
                    ['id' => 't14', 'stage' => 'Submitted', 'actor' => 'Fajar Setiawan', 'occurred_at' => '2026-07-22 13:05', 'state' => 'done', 'note' => null],
                    ['id' => 't15', 'stage' => 'Legal Review', 'actor' => 'Rizky Amalia', 'occurred_at' => '2026-07-25 09:40', 'state' => 'current', 'note' => null],
                    ['id' => 't16', 'stage' => 'Completed', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                ],
                'notes' => [
                    ['id' => 'n4', 'author' => 'Rizky Amalia', 'created_at_str' => '2026-07-30 11:11', 'body' => 'Perlu klausul lokasi penyimpanan data di wilayah Asia-Pasifik.'],
                ],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap6', 'approver' => 'Nadia Oktaviani', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-07-23 08:15', 'comment' => null],
                ],
            ],
            [
                'id' => '5',
                'request_code' => 'LGL-2026-0136',
                'submitted_at' => '2026-07-15',
                'unit' => 'Operations',
                'category' => 'Dispute / Litigation',
                'title' => 'Somasi keterlambatan penyelesaian pekerjaan kontraktor',
                'applicant' => 'Yusuf Maulana',
                'applicant_email' => 'yusuf.maulana@ssc.co.id',
                'priority' => 'urgent',
                'target_date' => '2026-07-30',
                'current_stage' => 'Waiting Review',
                'status' => 'waiting_review',
                'counterparty' => 'CV Mitra Bangun Sejati',
                'estimated_value' => null,
                'description' => 'Permohonan penyusunan somasi kepada kontraktor atas keterlambatan penyelesaian pekerjaan sipil melebihi 45 hari.',
                'attachments' => [
                    ['id' => 'a9', 'file_name' => 'berita-acara-keterlambatan.pdf', 'file_type' => 'PDF', 'file_size_kb' => 288, 'uploaded_by' => 'Yusuf Maulana', 'uploaded_at' => '2026-07-15'],
                    ['id' => 'a10', 'file_name' => 'foto-progres.zip', 'file_type' => 'ZIP', 'file_size_kb' => 4820, 'uploaded_by' => 'Yusuf Maulana', 'uploaded_at' => '2026-07-15'],
                ],
                'timeline' => [
                    ['id' => 't17', 'stage' => 'Submitted', 'actor' => 'Yusuf Maulana', 'occurred_at' => '2026-07-15 16:20', 'state' => 'done', 'note' => null],
                    ['id' => 't18', 'stage' => 'Waiting Review', 'actor' => 'Legal Intake', 'occurred_at' => '2026-07-16 08:00', 'state' => 'current', 'note' => 'Menunggu kelengkapan bukti pendukung.'],
                    ['id' => 't19', 'stage' => 'Completed', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                ],
                'notes' => [
                    ['id' => 'n5', 'author' => 'Legal Intake', 'created_at_str' => '2026-07-16 08:05', 'body' => 'Mohon lampirkan kontrak induk dan addendum terakhir.'],
                ],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap7', 'approver' => 'Gilang Saputra', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-07-15 17:10', 'comment' => null],
                ],
            ],
            [
                'id' => '6',
                'request_code' => 'LGL-2026-0130',
                'submitted_at' => '2026-06-30',
                'unit' => 'Procurement',
                'category' => 'Contract Review',
                'title' => 'Addendum perpanjangan sewa gudang regional',
                'applicant' => 'Melati Puspita',
                'applicant_email' => 'melati.puspita@ssc.co.id',
                'priority' => 'medium',
                'target_date' => '2026-07-18',
                'current_stage' => 'Completed',
                'status' => 'completed',
                'counterparty' => 'PT Graha Sentosa',
                'estimated_value' => 'IDR 780.000.000',
                'description' => 'Review dan finalisasi addendum perpanjangan sewa gudang untuk 24 bulan ke depan.',
                'attachments' => [
                    ['id' => 'a11', 'file_name' => 'addendum-draft.docx', 'file_type' => 'DOCX', 'file_size_kb' => 132, 'uploaded_by' => 'Melati Puspita', 'uploaded_at' => '2026-06-30'],
                ],
                'timeline' => [
                    ['id' => 't20', 'stage' => 'Submitted', 'actor' => 'Melati Puspita', 'occurred_at' => '2026-06-30 10:00', 'state' => 'done', 'note' => null],
                    ['id' => 't21', 'stage' => 'Legal Review', 'actor' => 'Rizky Amalia', 'occurred_at' => '2026-07-03 09:00', 'state' => 'done', 'note' => null],
                    ['id' => 't22', 'stage' => 'Completed', 'actor' => 'Legal Admin', 'occurred_at' => '2026-07-14 15:30', 'state' => 'done', 'note' => null],
                ],
                'notes' => [],
                'output_documents' => [
                    ['id' => 'd2', 'doc_number' => 'ADD/LGL/VII/2026/007', 'title' => 'Addendum II Perjanjian Sewa Gudang', 'doc_type' => 'Addendum', 'issued_at' => '2026-07-14'],
                    ['id' => 'd3', 'doc_number' => 'MEMO/LGL/VII/2026/019', 'title' => 'Legal Memo — Ringkasan Perubahan Klausul', 'doc_type' => 'Memo', 'issued_at' => '2026-07-14'],
                ],
                'approvals' => [
                    ['id' => 'ap8', 'approver' => 'Dwi Prasetyo', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-07-01 09:20', 'comment' => null],
                    ['id' => 'ap9', 'approver' => 'Legal Manager', 'role' => 'Legal Manager', 'decision' => 'approved', 'decided_at' => '2026-07-14 15:00', 'comment' => null],
                ],
            ],
            [
                'id' => '7',
                'request_code' => 'LGL-2026-0128',
                'submitted_at' => '2026-06-18',
                'unit' => 'Human Capital',
                'category' => 'Compliance',
                'title' => 'Penyesuaian kebijakan internal anti-suap',
                'applicant' => 'Sari Wulandari',
                'applicant_email' => 'sari.wulandari@ssc.co.id',
                'priority' => 'high',
                'target_date' => '2026-07-05',
                'current_stage' => 'Legal Review',
                'status' => 'in_process',
                'counterparty' => null,
                'estimated_value' => null,
                'description' => 'Review kebijakan anti-bribery & gratifikasi agar selaras dengan pedoman terbaru grup.',
                'attachments' => [
                    ['id' => 'a12', 'file_name' => 'kebijakan-existing.pdf', 'file_type' => 'PDF', 'file_size_kb' => 505, 'uploaded_by' => 'Sari Wulandari', 'uploaded_at' => '2026-06-18'],
                ],
                'timeline' => [
                    ['id' => 't23', 'stage' => 'Submitted', 'actor' => 'Sari Wulandari', 'occurred_at' => '2026-06-18 11:30', 'state' => 'done', 'note' => null],
                    ['id' => 't24', 'stage' => 'Legal Review', 'actor' => 'Andini Lestari', 'occurred_at' => '2026-06-24 10:15', 'state' => 'current', 'note' => null],
                    ['id' => 't25', 'stage' => 'Completed', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                ],
                'notes' => [
                    ['id' => 'n6', 'author' => 'Andini Lestari', 'created_at_str' => '2026-06-26 13:40', 'body' => 'Perlu penambahan mekanisme whistleblowing dan perlindungan pelapor.'],
                ],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap10', 'approver' => 'Hendra Wijaya', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-06-19 08:50', 'comment' => null],
                ],
            ],
            [
                'id' => '8',
                'request_code' => 'LGL-2026-0121',
                'submitted_at' => '2026-05-27',
                'unit' => 'Finance',
                'category' => 'Legal Opinion',
                'title' => 'Opini hukum atas skema pembiayaan supplier',
                'applicant' => 'Tio Ramadhan',
                'applicant_email' => 'tio.ramadhan@ssc.co.id',
                'priority' => 'low',
                'target_date' => '2026-06-20',
                'current_stage' => 'Completed',
                'status' => 'completed',
                'counterparty' => null,
                'estimated_value' => null,
                'description' => 'Kajian hukum skema supply chain financing bersama bank mitra.',
                'attachments' => [
                    ['id' => 'a13', 'file_name' => 'skema-scf.pdf', 'file_type' => 'PDF', 'file_size_kb' => 274, 'uploaded_by' => 'Tio Ramadhan', 'uploaded_at' => '2026-05-27'],
                ],
                'timeline' => [
                    ['id' => 't26', 'stage' => 'Submitted', 'actor' => 'Tio Ramadhan', 'occurred_at' => '2026-05-27 09:10', 'state' => 'done', 'note' => null],
                    ['id' => 't27', 'stage' => 'Legal Review', 'actor' => 'Rizky Amalia', 'occurred_at' => '2026-06-02 10:00', 'state' => 'done', 'note' => null],
                    ['id' => 't28', 'stage' => 'Completed', 'actor' => 'Legal Admin', 'occurred_at' => '2026-06-17 16:45', 'state' => 'done', 'note' => null],
                ],
                'notes' => [],
                'output_documents' => [
                    ['id' => 'd4', 'doc_number' => 'LO/LGL/VI/2026/004', 'title' => 'Legal Opinion — Supply Chain Financing', 'doc_type' => 'Legal Opinion', 'issued_at' => '2026-06-17'],
                ],
                'approvals' => [
                    ['id' => 'ap11', 'approver' => 'Hendra Wijaya', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-05-28 08:00', 'comment' => null],
                    ['id' => 'ap12', 'approver' => 'Legal Manager', 'role' => 'Legal Manager', 'decision' => 'approved', 'decided_at' => '2026-06-17 16:00', 'comment' => null],
                ],
            ],
            [
                'id' => '9',
                'request_code' => 'LGL-2026-0118',
                'submitted_at' => '2026-05-11',
                'unit' => 'Operations',
                'category' => 'Licensing',
                'title' => 'Perpanjangan izin operasional fasilitas distribusi',
                'applicant' => 'Yusuf Maulana',
                'applicant_email' => 'yusuf.maulana@ssc.co.id',
                'priority' => 'high',
                'target_date' => '2026-06-01',
                'current_stage' => 'Rejected',
                'status' => 'rejected',
                'counterparty' => null,
                'estimated_value' => null,
                'description' => 'Permohonan pendampingan hukum untuk perpanjangan izin operasional fasilitas distribusi regional.',
                'attachments' => [
                    ['id' => 'a14', 'file_name' => 'izin-lama.pdf', 'file_type' => 'PDF', 'file_size_kb' => 190, 'uploaded_by' => 'Yusuf Maulana', 'uploaded_at' => '2026-05-11'],
                ],
                'timeline' => [
                    ['id' => 't29', 'stage' => 'Submitted', 'actor' => 'Yusuf Maulana', 'occurred_at' => '2026-05-11 14:00', 'state' => 'done', 'note' => null],
                    ['id' => 't30', 'stage' => 'Rejected', 'actor' => 'Legal Intake', 'occurred_at' => '2026-05-13 09:30', 'state' => 'done', 'note' => 'Dokumen tidak lengkap, diminta pengajuan ulang.'],
                ],
                'notes' => [
                    ['id' => 'n7', 'author' => 'Legal Intake', 'created_at_str' => '2026-05-13 09:32', 'body' => 'Silakan ajukan ulang dengan melampirkan rekomendasi teknis dari dinas terkait.'],
                ],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap13', 'approver' => 'Gilang Saputra', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-05-12 08:10', 'comment' => null],
                    ['id' => 'ap14', 'approver' => 'Legal Intake', 'role' => 'Legal Reviewer', 'decision' => 'rejected', 'decided_at' => '2026-05-13 09:30', 'comment' => 'Kelengkapan dokumen belum memenuhi syarat.'],
                ],
            ],
            [
                'id' => '10',
                'request_code' => 'LGL-2026-0149',
                'submitted_at' => '2026-08-18',
                'unit' => 'IT & Digital',
                'category' => 'Agreement Drafting',
                'title' => 'NDA dengan mitra integrasi sistem',
                'applicant' => 'Fajar Setiawan',
                'applicant_email' => 'fajar.setiawan@ssc.co.id',
                'priority' => 'medium',
                'target_date' => '2026-08-26',
                'current_stage' => 'Waiting Review',
                'status' => 'waiting_review',
                'counterparty' => 'PT Sinergi Integrasi Data',
                'estimated_value' => null,
                'description' => 'Penyusunan NDA dua arah untuk tahap discovery proyek integrasi ERP.',
                'attachments' => [
                    ['id' => 'a15', 'file_name' => 'nda-template.docx', 'file_type' => 'DOCX', 'file_size_kb' => 78, 'uploaded_by' => 'Fajar Setiawan', 'uploaded_at' => '2026-08-18'],
                ],
                'timeline' => [
                    ['id' => 't31', 'stage' => 'Submitted', 'actor' => 'Fajar Setiawan', 'occurred_at' => '2026-08-18 10:22', 'state' => 'done', 'note' => null],
                    ['id' => 't32', 'stage' => 'Waiting Review', 'actor' => 'Legal Intake', 'occurred_at' => '2026-08-18 11:00', 'state' => 'current', 'note' => null],
                    ['id' => 't33', 'stage' => 'Completed', 'actor' => '—', 'occurred_at' => null, 'state' => 'pending', 'note' => null],
                ],
                'notes' => [],
                'output_documents' => [],
                'approvals' => [
                    ['id' => 'ap15', 'approver' => 'Nadia Oktaviani', 'role' => 'Unit Head', 'decision' => 'approved', 'decided_at' => '2026-08-18 10:45', 'comment' => null],
                ],
            ],
        ];

        DB::beginTransaction();
        try {
            foreach ($requests as $req) {
                LegalRequest::updateOrCreate(
                    ['id' => $req['id']],
                    [
                        'request_code' => $req['request_code'],
                        'submitted_at' => $req['submitted_at'],
                        'unit' => $req['unit'],
                        'category' => $req['category'],
                        'title' => $req['title'],
                        'applicant' => $req['applicant'],
                        'applicant_email' => $req['applicant_email'],
                        'priority' => $req['priority'],
                        'target_date' => $req['target_date'],
                        'current_stage' => $req['current_stage'],
                        'status' => $req['status'],
                        'counterparty' => $req['counterparty'],
                        'estimated_value' => $req['estimated_value'],
                        'description' => $req['description'],
                    ]
                );

                LegalAttachment::where('request_id', $req['id'])->delete();
                foreach ($req['attachments'] as $att) {
                    LegalAttachment::create([
                        'id' => $att['id'],
                        'request_id' => $req['id'],
                        'file_name' => $att['file_name'],
                        'file_type' => $att['file_type'],
                        'file_size_kb' => $att['file_size_kb'],
                        'uploaded_by' => $att['uploaded_by'],
                        'uploaded_at' => $att['uploaded_at'],
                    ]);
                }

                LegalTimelineEvent::where('request_id', $req['id'])->delete();
                foreach ($req['timeline'] as $tim) {
                    LegalTimelineEvent::create([
                        'id' => $tim['id'],
                        'request_id' => $req['id'],
                        'stage' => $tim['stage'],
                        'actor' => $tim['actor'],
                        'occurred_at' => $tim['occurred_at'],
                        'state' => $tim['state'],
                        'note' => $tim['note'],
                    ]);
                }

                LegalNote::where('request_id', $req['id'])->delete();
                foreach ($req['notes'] as $note) {
                    LegalNote::create([
                        'id' => $note['id'],
                        'request_id' => $req['id'],
                        'author' => $note['author'],
                        'created_at_str' => $note['created_at_str'],
                        'body' => $note['body'],
                    ]);
                }

                LegalOutputDocument::where('request_id', $req['id'])->delete();
                foreach ($req['output_documents'] as $doc) {
                    LegalOutputDocument::create([
                        'id' => $doc['id'],
                        'request_id' => $req['id'],
                        'doc_number' => $doc['doc_number'],
                        'title' => $doc['title'],
                        'doc_type' => $doc['doc_type'],
                        'issued_at' => $doc['issued_at'],
                    ]);
                }

                LegalApproval::where('request_id', $req['id'])->delete();
                foreach ($req['approvals'] as $app) {
                    LegalApproval::create([
                        'id' => $app['id'],
                        'request_id' => $req['id'],
                        'approver' => $app['approver'],
                        'role' => $app['role'],
                        'decision' => $app['decision'],
                        'decided_at' => $app['decided_at'],
                        'comment' => $app['comment'] ?? null,
                    ]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
