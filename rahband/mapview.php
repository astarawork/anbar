<?php
include_once('sar.php');
include_once('ca.php');

$query = "SELECT * FROM rkarbar WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
$result = mysqli_query($connection, $query);

$locations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $user = $row['user'];
    $color = '#3388ff'; // رنگ پیش‌فرض
    
    $query2 = "SELECT * FROM ruser WHERE user='$user'";
    $result2 = mysqli_query($connection, $query2);
    
    if ($row2 = mysqli_fetch_assoc($result2)) {
        $color = $row2['color'] ?? $color;
        $username = $row2['username'] ?? $user; // اگر فیلد username وجود نداشت، از user استفاده می‌کند
    }
    
    $locations[] = [
        'pelak' => $row['pelak'],
        'lat' => (float)$row['latitude'],
        'lng' => (float)$row['longitude'],
        'user' => $username ?? $user,
        'color' => $color
    ];
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نقشه موقعیت‌ها</title>
    
    <!-- استایل Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        #map {
            width: 100%;
            height: 100vh;
        }
        .pelak-info {
            padding: 10px;
            direction: rtl;
            text-align: right;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .pelak-info h3 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .pelak-info p {
            margin: 5px 0;
            color: #555;
        }
        .custom-marker-icon {
            background: transparent;
            border: none;
        }
        .user-color-display {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

<div id="map"></div>

<!-- اسکریپت Leaflet -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // ایجاد نقشه
    const map = L.map('map').setView([35.6892, 51.3890], 6);

    // اضافه کردن لایه نقشه از OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // تبدیل داده‌های PHP به JavaScript
    const locations = <?php echo json_encode($locations); ?>;
    
    // ایجاد گروه مارکرها
    const markers = L.layerGroup().addTo(map);
    
    // تابع برای ایجاد آیکون با رنگ دلخواه
    function createCustomIcon(color) {
        return L.divIcon({
            className: 'custom-marker-icon',
   html: `
            <svg width="25" height="41" viewBox="0 0 25 41" xmlns="http://www.w3.org/2000/svg">
                <!-- بدنه پین با حاشیه مشکی -->
                <path 
                    fill="${color}" 
                    stroke="#000" 
                    stroke-width="0.8" 
                    d="M12.5 0C5.6 0 0 5.6 0 12.5 0 25 12.5 41 12.5 41S25 25 25 12.5C25 5.6 19.4 0 12.5 0z"
                />
                <!-- دایره سفید با حاشیه مشکی -->
                <circle 
                    cx="12.5" 
                    cy="12.5" 
                    r="5" 
                    fill="white" 
                    stroke="#000" 
                    stroke-width="0.5"
                />
            </svg>
        `,
        iconSize: [25, 41],
        iconAnchor: [12.5, 41],
        popupAnchor: [0, -35]
		
        });
    }
    
    // اگر موقعیتی وجود دارد
    if (locations.length > 0) {
        const latlngs = [];
        
        locations.forEach(location => {
            const marker = L.marker([location.lat, location.lng], {
                icon: createCustomIcon(location.color)
            }).addTo(markers);
            
            latlngs.push([location.lat, location.lng]);
            
            // پنجره اطلاعات برای هر مارکر
            marker.bindPopup(`
                <div class="pelak-info">
                    <h3>پلاک: ${location.pelak}</h3>
                    <p>کاربر: ${location.user} 
                       <span class="user-color-display" style="background-color: ${location.color};"></span>
                    </p>
                    <p>عرض جغرافیایی: ${location.lat.toFixed(6)}</p>
                    <p>طول جغرافیایی: ${location.lng.toFixed(6)}</p>
                    <p>رنگ: <span style="color: ${location.color}">${location.color}</span></p>
                </div>
            `);
        });
        
        const bounds = L.latLngBounds(latlngs);
        map.fitBounds(bounds, {
            padding: [50, 50],
            maxZoom: 18
        });
    } else {
        map.setView([35.6892, 51.3890], 6);
        L.popup()
            .setLatLng([35.6892, 51.3890])
            .setContent('<div class="pelak-info">هیچ موقعیتی برای نمایش وجود ندارد</div>')
            .openOn(map);
    }

    // اضافه کردن کنترل اندازه‌گیری
    L.control.scale().addTo(map);
</script>

</body>
</html>