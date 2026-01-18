<?php

namespace App\Enums;

enum BlockType: string
{
    case Text = 'text';
    case Image = 'image';
    case Gallery = 'gallery';
    case Form = 'form';
    case Video = 'video';
    case Html = 'html';
    case Columns = 'columns';
    case Section = 'section';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Image => 'Image',
            self::Gallery => 'Gallery',
            self::Form => 'Form',
            self::Video => 'Video',
            self::Html => 'HTML',
            self::Columns => 'Columns',
            self::Section => 'Section',
        };
    }
}
