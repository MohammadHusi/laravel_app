<?php

namespace Tests\Unit;

use App\Models\AttendanceLog;
use PHPUnit\Framework\TestCase;

/**
 * White-box unit test untuk AttendanceLog (tanpa database).
 * TC-UNIT-LOG-01..03
 */
class AttendanceLogUnitTest extends TestCase
{
    /** TC-UNIT-LOG-01: pemetaan kode badge 5 status. */
    public function test_status_codes_map_correctly(): void
    {
        $this->assertSame('H', AttendanceLog::CODES['hadir']);
        $this->assertSame('S', AttendanceLog::CODES['sakit']);
        $this->assertSame('I', AttendanceLog::CODES['izin']);
        $this->assertSame('A', AttendanceLog::CODES['alpa']);
        $this->assertSame('T', AttendanceLog::CODES['terlambat']);
        $this->assertCount(5, AttendanceLog::CODES);
    }

    /** TC-UNIT-LOG-02: label manusiawi untuk 5 status. */
    public function test_status_labels_map_correctly(): void
    {
        $this->assertSame('Hadir', AttendanceLog::LABELS['hadir']);
        $this->assertSame('Terlambat', AttendanceLog::LABELS['terlambat']);
        $this->assertCount(5, AttendanceLog::LABELS);
    }

    /** TC-UNIT-LOG-03: code()/label() mengembalikan fallback '?'/status untuk nilai tak dikenal. */
    public function test_unknown_status_has_fallback(): void
    {
        $log = new AttendanceLog(['status' => 'tidak_dikenal']);

        $this->assertSame('?', $log->code());
        $this->assertSame('tidak_dikenal', $log->label());
    }
}
