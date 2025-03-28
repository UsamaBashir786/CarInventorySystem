<?php
// Error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/vendor/autoload.php';
// Check if TCPDF is installed via Composer
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    // Try to find TCPDF in common locations
    $tcpdfPaths = [
        'libraries/tcpdf/tcpdf.php',
        'tcpdf/tcpdf.php',
        '../tcpdf/tcpdf.php',
        'vendor/tecnickcom/tcpdf/tcpdf.php'
    ];
    
    $tcpdfLoaded = false;
    foreach ($tcpdfPaths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $tcpdfLoaded = true;
            break;
        }
    }
    
    if (!$tcpdfLoaded) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'TCPDF library not found. Please install TCPDF or check the path.']);
        exit;
    }
}

try {
    // Get vehicle data from POST request
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required data
    if (!$data || !isset($data['id']) || !isset($data['make']) || !isset($data['model'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required vehicle data']);
        exit;
    }

    // Create new PDF document with explicit parameters instead of constants
    // Parameters: orientation (P=portrait, L=landscape), unit (mm), format (A4, Letter)
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator('CentralAutogy');
    $pdf->SetAuthor('CentralAutogy');
    $pdf->SetTitle($data['year'] . ' ' . $data['make'] . ' ' . $data['model']);
    $pdf->SetSubject('Vehicle Brochure');
    $pdf->SetKeywords('car, vehicle, brochure, ' . $data['make'] . ', ' . $data['model']);

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set default monospaced font
    // Instead of PDF_FONT_MONOSPACED, use a specific font name
    $pdf->SetDefaultMonospacedFont('courier');

    // Set margins (left, top, right)
    $pdf->SetMargins(15, 15, 15);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(true, 15);

    // Set image scale factor (1.25 is standard)
    $pdf->setImageScale(1.25);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', 'B', 24);

    // Title
    $pdf->SetTextColor(36, 41, 46); // Dark gray color
    $pdf->Cell(0, 15, $data['year'] . ' ' . $data['make'] . ' ' . $data['model'], 0, 1, 'C');
    $pdf->Ln(5);

    // Add price as subtitle
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(79, 70, 229); // Indigo color
    $pdf->Cell(0, 10, '$' . number_format($data['price'], 2), 0, 1, 'C');
    $pdf->Ln(5);

    // Vehicle image (if available)
    if (isset($data['image']) && !empty($data['image'])) {
        // Try multiple approaches to locate the image
        $possiblePaths = [
            $data['image'],                                              // Direct path
            $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($data['image'], '/'), // Absolute path
            realpath(dirname(__FILE__) . '/' . ltrim($data['image'], '/')) // Relative to this script
        ];
        
        $imageFound = false;
        foreach ($possiblePaths as $imagePath) {
            if (file_exists($imagePath)) {
                // Get image dimensions to maintain aspect ratio
                list($width, $height) = getimagesize($imagePath);
                $aspectRatio = $width / $height;
                $newWidth = 180;
                $newHeight = $newWidth / $aspectRatio;
                
                // Add image to PDF
                $pdf->Image($imagePath, '', '', $newWidth, $newHeight, '', '', 'T', false, 300, 'C', false, false, 1, true);
                $pdf->Ln($newHeight + 10);
                $imageFound = true;
                break;
            }
        }
        
        if (!$imageFound) {
            // Add placeholder text if image not found
            $pdf->SetFont('helvetica', 'I', 12);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell(0, 10, 'Vehicle image not available', 0, 1, 'C');
            $pdf->Ln(5);
        }
    }

    $pdf->SetTextColor(0, 0, 0); // Reset to black

    // Vehicle details
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetFillColor(245, 247, 250); // Light gray background
    $pdf->Cell(0, 10, 'Vehicle Details', 0, 1, 'L', true);
    $pdf->Ln(5);

    $pdf->SetFont('helvetica', '', 12);

    // Create a details table
    $details = array(
        array('Year:', $data['year']),
        array('Make:', $data['make']),
        array('Model:', $data['model']),
        array('Price:', '$' . number_format($data['price'], 2)),
        array('Mileage:', number_format($data['mileage']) . ' mi'),
        array('VIN:', $data['vin']),
        array('Status:', $data['status'] ?? 'Available'),
        array('Body Style:', $data['body_style']),
        array('Transmission:', $data['transmission']),
        array('Fuel Type:', $data['fuel_type']),
        array('Engine:', $data['engine']),
        array('Drivetrain:', $data['drivetrain']),
        array('Exterior Color:', $data['exterior_color']),
        array('Interior Color:', $data['interior_color'])
    );

    // Set alternating row colors
    $altBg = false;
    foreach ($details as $detail) {
        // Set background color for alternating rows
        if ($altBg) {
            $pdf->SetFillColor(249, 250, 251);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(50, 8, $detail[0], 0, 0, 'L', true);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(0, 8, $detail[1], 0, 1, 'L', true);
        
        $altBg = !$altBg; // Toggle for next row
    }

    $pdf->Ln(10);

    // Description
    if (isset($data['description']) && !empty($data['description'])) {
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetFillColor(245, 247, 250); // Light gray background
        $pdf->Cell(0, 10, 'Description', 0, 1, 'L', true);
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', '', 12);
        // Clean and convert description text
        $description = nl2br(htmlspecialchars($data['description']));
        $pdf->writeHTML('<p>' . $description . '</p>');
    }

    $pdf->Ln(10);

    // Contact info with styled box
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetFillColor(79, 70, 229, 0.1); // Light indigo background
    $pdf->SetTextColor(79, 70, 229); // Indigo text
    $pdf->Cell(0, 10, 'Contact Information', 0, 1, 'L', true);
    $pdf->Ln(5);

    $pdf->SetTextColor(0, 0, 0); // Reset to black
    $pdf->SetFont('helvetica', '', 12);
    $contactInfo = '
    <div style="border: 1px solid #e5e7eb; padding: 10px; border-radius: 5px;">
        <p>For more information about this vehicle, please contact us:</p>
        <p><b>CentralAutogy</b><br>
        123 Central Avenue, Autogy City, CA 90210<br>
        Phone: (800) 123-4567<br>
        Email: info@centralautogy.com<br>
        Website: www.centralautogy.com</p>
    </div>';
    $pdf->writeHTML($contactInfo);

    // Add disclaimer at the bottom
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->writeHTML('
    <p>Disclaimer: This brochure is for informational purposes only. Actual vehicle details may vary. 
    Please confirm all information with a sales representative. 
    Generated on ' . date('F j, Y') . ' by CentralAutogy inventory system.</p>
    ');

    // Output the PDF
    $pdf->Output($data['year'] . '_' . $data['make'] . '_' . $data['model'] . '_brochure.pdf', 'I');

} catch (Exception $e) {
    // Handle any exceptions
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'PDF generation error: ' . $e->getMessage()]);
    exit;
}