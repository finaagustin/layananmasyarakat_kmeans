<?php

namespace App\Support;

/**
 * Penulis file XLSX minimal tanpa dependency composer tambahan.
 *
 * Sebuah file .xlsx pada dasarnya adalah arsip ZIP berisi beberapa
 * berkas XML (format Office Open XML). Kelas ini menyusun struktur
 * minimal tersebut secara manual menggunakan ekstensi ZipArchive
 * bawaan PHP, sehingga tidak memerlukan package composer tambahan
 * seperti PhpSpreadsheet atau Maatwebsite/Excel.
 *
 * Setiap sel ditulis sebagai inline string (<is><t>...</t></is>),
 * sehingga tidak perlu mengelola berkas sharedStrings.xml terpisah.
 */
class SimpleXlsxWriter
{
    /** @var array<int, array<int, string>> */
    protected array $rows = [];

    /**
     * Menambahkan satu baris data.
     *
     * @param array<int, string|int|float|null> $cells
     */
    public function addRow(array $cells): static
    {
        $this->rows[] = $cells;

        return $this;
    }

    /**
     * Menulis dan mengunduh file XLSX langsung sebagai response.
     */
    public function download(string $fileName): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');

        $this->save($tempPath);

        return response()
            ->download($tempPath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * Menyimpan hasil susunan baris ke sebuah file .xlsx pada path tertentu.
     */
    public function save(string $path): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file XLSX sementara.');
        }

        $zip->addEmptyDir('_rels');
        $zip->addEmptyDir('docProps');
        $zip->addEmptyDir('xl');
        $zip->addEmptyDir('xl/_rels');
        $zip->addEmptyDir('xl/worksheets');

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->relsXml());
        $zip->addFromString('docProps/core.xml', $this->coreXml());
        $zip->addFromString('docProps/app.xml', $this->appXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml());

        $zip->close();
    }

    protected function contentTypesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
    <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
    <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML;
    }

    protected function relsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
    <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML;
    }

    protected function coreXml(): string
    {
        $now = now()->toAtomString();

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <dc:creator>SIPETRUK DICIPTABINTAR</dc:creator>
    <dcterms:created xsi:type="dcterms:W3CDTF">{$now}</dcterms:created>
    <dcterms:modified xsi:type="dcterms:W3CDTF">{$now}</dcterms:modified>
</cp:coreProperties>
XML;
    }

    protected function appXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
    <Application>SIPETRUK</Application>
</Properties>
XML;
    }

    protected function workbookXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="Data Layanan" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    protected function workbookRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    protected function stylesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><sz val="11"/><name val="Calibri"/><b/></font>
    </fonts>
    <fills count="1"><fill><patternFill patternType="none"/></fill></fills>
    <borders count="1"><border/></borders>
    <cellStyleXfs count="1"><xf numFmtId="0" fontId="0"/></cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" xfId="0"/>
    </cellXfs>
</styleSheet>
XML;
    }

    protected function columnLetter(int $index): string
    {
        $letter = '';

        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = intdiv($index, 26) - 1;
        }

        return $letter;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected function sheetXml(): string
    {
        $xmlRows = '';

        foreach ($this->rows as $rowIndex => $cells) {

            $rowNumber = $rowIndex + 1;

            $xmlCells = '';

            foreach ($cells as $colIndex => $value) {

                $ref = $this->columnLetter($colIndex) . $rowNumber;

                $styleIndex = $rowIndex === 0 ? 1 : 0;

                $text = $this->escape((string) ($value ?? ''));

                $xmlCells .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"{$styleIndex}\"><is><t xml:space=\"preserve\">{$text}</t></is></c>";
            }

            $xmlRows .= "<row r=\"{$rowNumber}\">{$xmlCells}</row>";
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <sheetData>{$xmlRows}</sheetData>
</worksheet>
XML;
    }
}
