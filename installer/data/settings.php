<?php

/*
Class to provide settings data
*/

class Settings
{
    public static $data = '[
    {
        "key": "project",
        "name": "The Project Name",
        "theme": "default",
        "notes": "Some notes"
    },
    {
        "key": "timing",
        "nextUpdateIntervalMin": 1,
        "nextUpdateIntervalMax": 3
    },
    {
        "key": "images",
        "cloud_name": "",
        "upload_preset": "",
        "max_image_width": "800",
        "max_image_height": "800",
        "folder": ""
    },
    {
        "key": "styles",
        "value": [
            {
                "name": "Note",
                "class": "note",
                "tags": ["p"]
            }
        ]
    }
]';
}
