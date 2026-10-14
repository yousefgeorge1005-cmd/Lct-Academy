<?php
/**
 * LCT Academy — minimal, dependency-free XLSX writer.
 *
 * Writes real .xlsx files (a zip of small XML parts) using only PHP's
 * built-in ZipArchive extension — no Composer, no external libraries.
 * Intentionally narrow in scope: bold/bordered header row, bordered data
 * cells, bordered empty "box" cells (for hand-filled attendance), a merged
 * title row, and a right-to-left sheet view for Arabic content. That's all
 * this project's roster/attendance export needs.
 */

class SimpleXlsxWriter {
    const STYLE_DEFAULT = 0;
    const STYLE_TITLE    = 1;
    const STYLE_HEADER   = 2;
    const STYLE_CELL     = 3;
    const STYLE_BOX      = 4;

    private $rows = [];
    private $colWidths = [];
    private $sheetName = 'Sheet1';
    private $rtl = true;

    public function setSheetName($name) {
        // Excel sheet names: max 31 chars, no : \ / ? * [ ]
        $name = preg_replace('/[:\\\\\/\?\*\[\]]/', ' ', (string)$name);
        $this->sheetName = mb_substr(trim($name) !== '' ? $name : 'Sheet1', 0, 31);
    }

    public function setRtl($rtl) { $this->rtl = (bool)$rtl; }

    public function setColumnWidths(array $widths) { $this->colWidths = $widths; }

    /**
     * @param array $cells Each item: ['value' => string|number, 'style' => self::STYLE_*]
     * @param array $opts  ['height' => float, 'merge' => int (colspan from first cell)]
     */
    public function addRow(array $cells, array $opts = []) {
        $this->rows[] = ['cells' => $cells, 'height' => $opts['height'] ?? null, 'merge' => $opts['merge'] ?? null];
    }

    private function colLetter($n) {
        $letter = '';
        while ($n > 0) {
            $rem = ($n - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $n = intdiv($n - 1, 26);
        }
        return $letter;
    }

    private function xmlEscape($s) {
        return htmlspecialchars((string)$s, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function buildSheetXml() {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<sheetViews><sheetView rightToLeft="' . ($this->rtl ? '1' : '0') . '" workbookViewId="0"/></sheetViews>';

        if (!empty($this->colWidths)) {
            $xml .= '<cols>';
            foreach ($this->colWidths as $i => $w) {
                $col = $i + 1;
                $xml .= '<col min="' . $col . '" max="' . $col . '" width="' . (float)$w . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }

        $xml .= '<sheetData>';
        $mergeCells = [];
        foreach ($this->rows as $rIdx => $row) {
            $r = $rIdx + 1;
            $heightAttr = $row['height'] ? ' ht="' . (float)$row['height'] . '" customHeight="1"' : '';
            $xml .= '<row r="' . $r . '"' . $heightAttr . '>';
            foreach ($row['cells'] as $cIdx => $cell) {
                $c = $cIdx + 1;
                $ref = $this->colLetter($c) . $r;
                $style = $cell['style'] ?? self::STYLE_DEFAULT;
                $value = $cell['value'] ?? '';
                // Anything numeric-looking that starts with a leading zero (phone numbers,
                // national IDs) must be written as text — as a real number, Excel would
                // silently drop that leading zero.
                $looksNumeric = is_numeric($value) && !preg_match('/^0\d/', (string)$value);
                if ($value === '') {
                    $xml .= '<c r="' . $ref . '" s="' . $style . '"/>';
                } elseif ($looksNumeric) {
                    $xml .= '<c r="' . $ref . '" s="' . $style . '"><v>' . $this->xmlEscape($value) . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $this->xmlEscape($value) . '</t></is></c>';
                }
            }
            $xml .= '</row>';
            if (!empty($row['merge']) && $row['merge'] > 1) {
                $endCol = $this->colLetter(count($row['cells']) > 0 ? $row['merge'] : 1);
                $mergeCells[] = $this->colLetter(1) . $r . ':' . $endCol . $r;
            }
        }
        $xml .= '</sheetData>';

        if (!empty($mergeCells)) {
            $xml .= '<mergeCells count="' . count($mergeCells) . '">';
            foreach ($mergeCells as $m) $xml .= '<mergeCell ref="' . $m . '"/>';
            $xml .= '</mergeCells>';
        }

        $xml .= '</worksheet>';
        return $xml;
    }

    private function buildStylesXml() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="3">'
        . '<font><sz val="11"/><name val="Calibri"/></font>'
        . '<font><b/><sz val="16"/><name val="Calibri"/><color rgb="FF1F2937"/></font>'
        . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
        . '</fonts>'
        . '<fills count="3">'
        . '<fill><patternFill patternType="none"/></fill>'
        . '<fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF3E3226"/><bgColor indexed="64"/></patternFill></fill>'
        . '</fills>'
        . '<borders count="2">'
        . '<border><left/><right/><top/><bottom/><diagonal/></border>'
        . '<border><left style="thin"><color rgb="FFBBBBBB"/></left><right style="thin"><color rgb="FFBBBBBB"/></right><top style="thin"><color rgb="FFBBBBBB"/></top><bottom style="thin"><color rgb="FFBBBBBB"/></bottom><diagonal/></border>'
        . '</borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="5">'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
        . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
        . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
        . '</cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>';
    }

    /** Write the .xlsx to $path. Returns true on success, false on failure (e.g. no ZipArchive). */
    public function save($path) {
        if (!class_exists('ZipArchive')) return false;

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) return false;

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>'
        );

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>'
        );

        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>'
        );

        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . $this->xmlEscape($this->sheetName) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>'
        );

        $zip->addFromString('xl/styles.xml', $this->buildStylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->buildSheetXml());

        return $zip->close();
    }
}
