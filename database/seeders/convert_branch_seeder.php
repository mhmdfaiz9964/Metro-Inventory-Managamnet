<?php

// Read the original file
$filePath = __DIR__ . '/BranchSeeder.php';
$content = file_get_contents($filePath);

// Extract the JSON part (everything between [ and ])
preg_match('/\$banksData\s*=\s*(\[.*?\]);/s', $content, $matches);

if (!empty($matches[1])) {
    $jsonData = $matches[1];
    
    // Fix JSON syntax issues
    $jsonData = str_replace(['"', '\''], '"', $jsonData); // Normalize quotes
    $jsonData = preg_replace('/(\w+):/', '"$1":', $jsonData); // Add quotes to keys
    $jsonData = str_replace(["\n", "\r", "\t"], '', $jsonData); // Remove newlines and tabs
    $jsonData = preg_replace('/\s+/', ' ', $jsonData); // Replace multiple spaces with single space
    
    // Convert JSON to PHP array
    $phpArray = json_decode($jsonData, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        // Generate PHP array code
        $phpCode = "<?php\n\nnamespace Database\\Seeders;\n\nuse Illuminate\\Database\\Console\\Seeds\\WithoutModelEvents;\nuse Illuminate\\Database\\Seeder;\nuse Illuminate\\Support\\Facades\\DB;\n\nclass BranchSeeder extends Seeder\n{\n    /**\n     * Run the database seeds.\n     */\n    public function run(): void\n    {\n        \$banksData = " . var_export($phpArray, true) . ";\n\n        foreach (\$banksData as \$bankId => \$branches) {\n            foreach (\$branches as \$branch) {\n                DB::table('branches')->updateOrInsert(\n                    ['bank_id' => \$branch['bankID'], 'branch_id' => \$branch['ID']],\n                    [\n                        'name' => \$branch['name'],\n                        'created_at' => now(),\n                        'updated_at' => now(),\n                    ]\n                );\n            }\n        }\n    }\n}\n";
        
        // Save to a new file
        $newFilePath = __DIR__ . '/BranchSeederFixed.php';
        file_put_contents($newFilePath, $phpCode);
        
        echo "Conversion complete. New file saved as: " . basename($newFilePath) . "\n";
    } else {
        echo "Error decoding JSON: " . json_last_error_msg() . "\n";
    }
} else {
    echo "Could not find the banks data array in the file.\n";
}
