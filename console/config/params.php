<?php
return [
    'adminEmail' => 'admin@example.com',
    'migrate_tvod1' => [
        'catchup_min_day' => 35, // 35 days
        'catchup_process_timeout' => 12, // 12 hours value in hours
        'content_process_timeout' => 12, // 12 hours value in hours
        'force_video' => 'false', // d/m/Y H:i:s Dung truong hop can dong bo lai video tu 1 thoi diem. Format: 'd/m/Y H:i:s, vi du: 1/11/2016 00:00:00
        'force_channel' => 'false', // Dung truong hop can dong bo lai channel tu 1 thoi diem. Format: 'd/m/Y H:i:s, vi du: 1/11/2016 00:00:00
        'force_catchup' => 'false', // d/m/Y H:i:s Dung truong hop can dong bo lai catchup tu 1 thoi diem. Format: 'd/m/Y H:i:s, vi du: 1/11/2016 00:00:00
    ]
];
