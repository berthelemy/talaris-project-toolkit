<?php

/**
 * Persistence model for Theme Settings Model.
 */

namespace App\Models;

use CodeIgniter\Model;

/**
 * Persistence model for theme and branding settings.
 */
class ThemeSettingsModel extends Model
{
    protected $table            = 'theme_settings';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $allowedFields    = [
        'site_title',
        'logo_path',
        'heading_font',
        'body_font',
        'primary_color',
        'secondary_color',
        'background_color',
        'text_color',
    ];
}
