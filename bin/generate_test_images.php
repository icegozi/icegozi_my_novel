<?php

// Ensure output directories exist
$baseDir = dirname(__DIR__) . '/public/images';
@mkdir($baseDir, 0755, true);
@mkdir($baseDir . '/covers', 0755, true);
@mkdir($baseDir . '/comics', 0755, true);
@mkdir($baseDir . '/avatars', 0755, true);

function drawVerticalGradient($im, $width, $height, $startColor, $endColor) {
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / (float)$height;
        $r = (int)($startColor[0] + ($endColor[0] - $startColor[0]) * $ratio);
        $g = (int)($startColor[1] + ($endColor[1] - $startColor[1]) * $ratio);
        $b = (int)($startColor[2] + ($endColor[2] - $startColor[2]) * $ratio);
        $col = imagecolorallocate($im, $r, $g, $b);
        imageline($im, 0, $y, $width, $y, $col);
    }
}

function addDecorativeCover($im, $width, $height, $title, $genre, $accentRgb) {
    $accent = imagecolorallocate($im, $accentRgb[0], $accentRgb[1], $accentRgb[2]);
    $white = imagecolorallocate($im, 255, 255, 255);
    $gold = imagecolorallocate($im, 245, 158, 11);
    $transWhite = imagecolorallocatealpha($im, 255, 255, 255, 100);
    $cardBg = imagecolorallocatealpha($im, 15, 23, 42, 60);

    // Inner card border
    imagesetthickness($im, 3);
    imagerectangle($im, 30, 30, $width - 30, $height - 30, $accent);
    imagesetthickness($im, 1);
    imagerectangle($im, 38, 38, $width - 38, $height - 38, $transWhite);

    // Geometric diamond in center
    $cx = (int)($width / 2);
    $cy = (int)($height * 0.42);
    $diamondPoints = [
        $cx, $cy - 80,
        $cx + 80, $cy,
        $cx, $cy + 80,
        $cx - 80, $cy
    ];
    imagefilledpolygon($im, $diamondPoints, $cardBg);
    imagesetthickness($im, 2);
    imagepolygon($im, $diamondPoints, $accent);

    // Inner concentric ring
    imageellipse($im, $cx, $cy, 110, 110, $gold);
    imageellipse($im, $cx, $cy, 50, 50, $white);

    // Stars / accents
    for ($i = 0; $i < 40; $i++) {
        $sx = rand(45, $width - 45);
        $sy = rand(45, $height - 45);
        $starCol = imagecolorallocatealpha($im, 255, 255, 255, rand(40, 90));
        imagefilledellipse($im, $sx, $sy, rand(2, 4), rand(2, 4), $starCol);
    }

    // Genre Tag Banner
    $bannerW = 200;
    $bannerH = 34;
    $bx1 = $cx - ($bannerW / 2);
    $by1 = 90;
    imagefilledrectangle($im, (int)$bx1, $by1, (int)($bx1 + $bannerW), $by1 + $bannerH, $cardBg);
    imagerectangle($im, (int)$bx1, $by1, (int)($bx1 + $bannerW), $by1 + $bannerH, $accent);
    
    // Draw Text with built-in fonts (font 5 is 9x15 px)
    $genreText = strtoupper($genre);
    $tw = strlen($genreText) * 9;
    imagestring($im, 5, (int)($cx - ($tw / 2)), $by1 + 9, $genreText, $gold);

    // Title text banner at bottom
    $titleBoxY = (int)($height * 0.72);
    imagefilledrectangle($im, 50, $titleBoxY, $width - 50, $titleBoxY + 110, $cardBg);
    imagesetthickness($im, 2);
    imagerectangle($im, 50, $titleBoxY, $width - 50, $titleBoxY + 110, $accent);

    $titleW = strlen($title) * 9;
    imagestring($im, 5, (int)($cx - ($titleW / 2)), $titleBoxY + 25, $title, $white);

    $sub = "3D NOVEL EXPERIENCE - 2026";
    $sw = strlen($sub) * 9;
    imagestring($im, 4, (int)($cx - ($sw / 2)), $titleBoxY + 60, $sub, $gold);
}

// 1. Default Cover (600x800)
$im = imagecreatetruecolor(600, 800);
drawVerticalGradient($im, 600, 800, [15, 23, 42], [30, 41, 59]);
addDecorativeCover($im, 600, 800, "NOVEL ENGINE 3D", "KIET TAC NGUYEN TAC", [99, 102, 241]);
imagewebp($im, $baseDir . '/default_cover.webp', 90);
imagedestroy($im);
echo "Created default_cover.webp\n";

// 2. Genre Covers (600x800)
$genreConfigs = [
    'tien-hiep' => [
        'title' => 'CUU CHAU TIEN TON',
        'genre' => 'TIEN HIEP TU CHAN',
        'top' => [20, 20, 60],
        'bottom' => [49, 46, 129],
        'accent' => [129, 140, 248]
    ],
    'huyen-huyen' => [
        'title' => 'LINH VU THIEN HA',
        'genre' => 'HUYEN HUYEN MA PHAP',
        'top' => [15, 35, 40],
        'bottom' => [6, 78, 59],
        'accent' => [52, 211, 153]
    ],
    'comic-webtoon' => [
        'title' => 'VO THAN TAI SINH',
        'genre' => 'WEBTOON COLOR COMIC',
        'top' => [69, 10, 10],
        'bottom' => [153, 27, 27],
        'accent' => [248, 113, 113]
    ],
    'khoa-huyen' => [
        'title' => 'KY NGUYEN TINH THAN',
        'genre' => 'KHOA HUYEN TUONG LAI',
        'top' => [8, 47, 73],
        'bottom' => [12, 74, 110],
        'accent' => [56, 189, 248]
    ],
    'do-thi' => [
        'title' => 'THAN Y HA SON',
        'genre' => 'DO THI TU CHAN',
        'top' => [30, 20, 50],
        'bottom' => [76, 29, 149],
        'accent' => [192, 132, 252]
    ],
    'kiem-hiep' => [
        'title' => 'THIEN LONG GIANG HO',
        'genre' => 'KIEM HIEP CO DIEN',
        'top' => [45, 25, 15],
        'bottom' => [120, 53, 15],
        'accent' => [251, 191, 36]
    ],
];

foreach ($genreConfigs as $slug => $cfg) {
    $im = imagecreatetruecolor(600, 800);
    drawVerticalGradient($im, 600, 800, $cfg['top'], $cfg['bottom']);
    addDecorativeCover($im, 600, 800, $cfg['title'], $cfg['genre'], $cfg['accent']);
    imagewebp($im, $baseDir . "/covers/{$slug}.webp", 90);
    imagedestroy($im);
    echo "Created covers/{$slug}.webp\n";
}

// 3. Comic Panels (800x1200)
$comicPanels = [
    'panel-1' => [
        'title' => 'PANEL 1: DOAN HON NHAI TRUNG SINH',
        'subtitle' => 'SAM CHOP XE TOAC BIEN BIEN GIOI',
        'top' => [17, 24, 39],
        'bottom' => [30, 58, 138],
        'accent' => [96, 165, 250]
    ],
    'panel-2' => [
        'title' => 'PANEL 2: TU BAO CUU PHAM KIM DAN',
        'subtitle' => 'LINH LUC BUNG NO TRONG THACH THAT',
        'top' => [67, 20, 7],
        'bottom' => [154, 52, 18],
        'accent' => [251, 146, 60]
    ],
    'panel-3' => [
        'title' => 'PANEL 3: CUU PHAM VO HON THUC TINH',
        'subtitle' => 'CHI TON VO THAN QUET NGANG CUONG DICH',
        'top' => [15, 23, 42],
        'bottom' => [88, 28, 135],
        'accent' => [168, 85, 247]
    ],
];

foreach ($comicPanels as $name => $p) {
    $im = imagecreatetruecolor(800, 1200);
    drawVerticalGradient($im, 800, 1200, $p['top'], $p['bottom']);
    
    $accent = imagecolorallocate($im, $p['accent'][0], $p['accent'][1], $p['accent'][2]);
    $white = imagecolorallocate($im, 255, 255, 255);
    $cardBg = imagecolorallocatealpha($im, 15, 23, 42, 40);

    // Speed lines & rays
    for ($i = 0; $i < 60; $i++) {
        $lineCol = imagecolorallocatealpha($im, 255, 255, 255, rand(80, 120));
        imageline($im, 400, 600, rand(0, 800), rand(0, 1200), $lineCol);
    }

    // Dynamic panel frame
    imagesetthickness($im, 8);
    imagerectangle($im, 40, 40, 760, 1160, $accent);
    imagesetthickness($im, 2);
    imagerectangle($im, 55, 55, 745, 1145, $white);

    // Energy core orb
    for ($r = 160; $r > 0; $r -= 8) {
        $orbCol = imagecolorallocatealpha($im, $p['accent'][0], $p['accent'][1], $p['accent'][2], (int)(110 - ($r * 0.5)));
        imagefilledellipse($im, 400, 600, $r * 2, $r * 2, $orbCol);
    }
    imagefilledellipse($im, 400, 600, 60, 60, $white);

    // Text box banner
    imagefilledrectangle($im, 80, 960, 720, 1100, $cardBg);
    imagesetthickness($im, 3);
    imagerectangle($im, 80, 960, 720, 1100, $accent);

    $tw = strlen($p['title']) * 9;
    imagestring($im, 5, (int)(400 - ($tw / 2)), 995, $p['title'], $white);

    $sw = strlen($p['subtitle']) * 9;
    imagestring($im, 4, (int)(400 - ($sw / 2)), 1035, $p['subtitle'], $accent);

    $chapterSign = "NOVEL ENGINE - WEBTOON HIGH RESOLUTION PANEL";
    $cw = strlen($chapterSign) * 9;
    imagestring($im, 4, (int)(400 - ($cw / 2)), 1065, $chapterSign, $white);

    imagewebp($im, $baseDir . "/comics/{$name}.webp", 90);
    imagedestroy($im);
    echo "Created comics/{$name}.webp\n";
}

// 4. Avatars (200x200)
$avatars = [
    'admin' => ['label' => 'ADMIN', 'tag' => 'AD', 'top' => [67, 56, 202], 'bottom' => [30, 27, 75], 'accent' => [245, 158, 11]],
    'author' => ['label' => 'AUTHOR', 'tag' => 'TG', 'top' => [13, 148, 136], 'bottom' => [19, 78, 74], 'accent' => [20, 184, 166]],
    'reader' => ['label' => 'READER', 'tag' => 'DG', 'top' => [2, 132, 199], 'bottom' => [12, 74, 110], 'accent' => [56, 189, 248]],
];

foreach ($avatars as $name => $av) {
    $im = imagecreatetruecolor(200, 200);
    drawVerticalGradient($im, 200, 200, $av['top'], $av['bottom']);

    $accent = imagecolorallocate($im, $av['accent'][0], $av['accent'][1], $av['accent'][2]);
    $white = imagecolorallocate($im, 255, 255, 255);
    $gold = imagecolorallocate($im, 245, 158, 11);

    // Circular border
    imagesetthickness($im, 5);
    imagearc($im, 100, 100, 185, 185, 0, 360, $accent);
    imagesetthickness($im, 2);
    imagearc($im, 100, 100, 172, 172, 0, 360, $white);

    // Inner circle
    $innerCol = imagecolorallocatealpha($im, 15, 23, 42, 40);
    imagefilledellipse($im, 100, 100, 140, 140, $innerCol);

    // Initial letters centered (big)
    $tag = $av['tag'];
    $tw = strlen($tag) * 9;
    imagestring($im, 5, (int)(100 - ($tw / 2)), 82, $tag, $white);

    // Label banner
    $label = $av['label'];
    $lw = strlen($label) * 9;
    imagestring($im, 4, (int)(100 - ($lw / 2)), 110, $label, $gold);

    imagewebp($im, $baseDir . "/avatars/{$name}.webp", 95);
    imagedestroy($im);
    echo "Created avatars/{$name}.webp\n";
}

echo "All images successfully generated!\n";
