<?php
require_once 'vendor/autoload.php';

// Catch all errors and output as JSON
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
  header('Content-Type: application/json');
  http_response_code(500);
  echo json_encode([
    'error' => "Error ($errno): $errstr in $errfile on line $errline",
    'type' => 'php_error'
  ]);
  exit;
});

try {
  // Get vehicle data
  $jsonData = file_get_contents('php://input');
  $data = json_decode($jsonData, true);

  if (!$data) {
    throw new Exception("Invalid JSON data");
  }

  // Create a new PDF document
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
  $pdf->SetDefaultMonospacedFont('courier');

  // Set margins
  $pdf->SetMargins(15, 15, 15);

  // Set auto page breaks
  $pdf->SetAutoPageBreak(true, 15);

  // Add a page
  $pdf->AddPage();

  // Set font
  $pdf->SetFont('helvetica', 'B', 24);

  // Title
  $pdf->SetTextColor(36, 41, 46); // Dark gray color
  $pdf->Cell(0, 15, $data['year'] . ' ' . $data['make'] . ' ' . $data['model'], 0, 1, 'C');
  $pdf->Ln(5);

  // Add price as subtitle - with null checking
  $pdf->SetFont('helvetica', 'B', 18);
  $pdf->SetTextColor(79, 70, 229); // Indigo color
  if (isset($data['price']) && $data['price'] !== null && $data['price'] != 0) {
    $pdf->Cell(0, 10, '$' . number_format((float)$data['price'], 2), 0, 1, 'C');
  } else {
    $pdf->Cell(0, 10, 'Contact for price', 0, 1, 'C');
  }
  $pdf->Ln(5);

  // Image handling - very cautious approach
  if (isset($data['image']) && !empty($data['image'])) {
    try {
      // Only attempt to add an image if it exists and is accessible
      if (file_exists($data['image'])) {
        $imageInfo = @getimagesize($data['image']);
        if ($imageInfo !== false) {
          // Add image to PDF with fixed dimensions
          $pdf->Image($data['image'], null, null, 180, 120, '', '', 'T', false, 300, 'C', false, false, 1);
          $pdf->Ln(125); // space after image
        }
      }
    } catch (Exception $imageEx) {
      // Just skip image on any error
    }
  }

  $pdf->SetTextColor(0, 0, 0); // Reset text color to black

  // Vehicle details
  $pdf->SetFont('helvetica', 'B', 16);
  $pdf->SetFillColor(245, 247, 250); // Light gray background
  $pdf->Cell(0, 10, 'Vehicle Details', 0, 1, 'L', true);
  $pdf->Ln(5);

  $pdf->SetFont('helvetica', '', 12);

  // Create details array with safe value handling
  $details = [
    ['Year:', isset($data['year']) ? $data['year'] : 'Not specified'],
    ['Make:', isset($data['make']) ? $data['make'] : 'Not specified'],
    ['Model:', isset($data['model']) ? $data['model'] : 'Not specified'],
    ['Price:', (isset($data['price']) && $data['price'] !== null && $data['price'] != 0) ?
      '$' . number_format((float)$data['price'], 2) : 'Contact for price'],
    ['Mileage:', isset($data['mileage']) ? number_format($data['mileage']) . ' mi' : 'Not specified'],
    ['VIN:', isset($data['vin']) ? $data['vin'] : 'Not available'],
    ['Status:', $data['status'] ?? 'Available']
  ];

  // Add additional details if available
  if (isset($data['body_style'])) {
    $details[] = ['Body Style:', $data['body_style']];
  }
  if (isset($data['transmission'])) {
    $details[] = ['Transmission:', $data['transmission']];
  }
  if (isset($data['fuel_type'])) {
    $details[] = ['Fuel Type:', $data['fuel_type']];
  }
  if (isset($data['engine'])) {
    $details[] = ['Engine:', $data['engine']];
  }
  if (isset($data['drivetrain'])) {
    $details[] = ['Drivetrain:', $data['drivetrain']];
  }
  if (isset($data['exterior_color'])) {
    $details[] = ['Exterior Color:', $data['exterior_color']];
  }
  if (isset($data['interior_color'])) {
    $details[] = ['Interior Color:', $data['interior_color']];
  }

  // Output details table
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
    $pdf->writeHTML('<p>' . nl2br(htmlspecialchars($data['description'])) . '</p>');
  }

  // Add disclaimer
  $pdf->Ln(10);
  $pdf->SetFont('helvetica', 'I', 9);
  $pdf->SetTextColor(150, 150, 150);
  $pdf->writeHTML('
    <p>Disclaimer: This brochure is for informational purposes only. Actual vehicle details may vary. 
    Please confirm all information with a sales representative. 
    Generated on ' . date('F j, Y') . ' by CentralAutogy inventory system.</p>
    ');

  // Output as download - using the temp file approach that worked
  header('Content-Type: application/pdf');
  header('Cache-Control: max-age=0');
  header('Content-Disposition: attachment; filename="' . $data['year'] . '_' . $data['make'] . '_' . $data['model'] . '_brochure.pdf"');

  // Write to a temporary file first, then read and output
  $tempFile = tempnam(sys_get_temp_dir(), 'brochure_');
  $pdf->Output($tempFile, 'F');

  // Read and output the file
  readfile($tempFile);
  unlink($tempFile); // Delete the temp file

} catch (Exception $e) {
  header('Content-Type: application/json');
  http_response_code(500);
  echo json_encode([
    'error' => 'PDF generation error: ' . $e->getMessage(),
    'type' => 'exception',
    'trace' => $e->getTraceAsString()
  ]);
}
