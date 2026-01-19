<?php

namespace App\Enums;

enum BlockType: string
{
    case Text = 'text';
    case Heading = 'heading';
    case Image = 'image';
    case Gallery = 'gallery';
    case Form = 'form';
    case Video = 'video';
    case Html = 'html';
    case Columns = 'columns';
    case Section = 'section';
    case Container = 'container';
    case Spacer = 'spacer';
    case Separator = 'separator';
    case Button = 'button';
    case Accordion = 'accordion';
    case Tabs = 'tabs';
    case CardGrid = 'card_grid';
    case SippSchedule = 'sipp_schedule';
    case NewsGrid = 'news_grid';
    case DocumentList = 'document_list';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text',
            self::Heading => 'Heading',
            self::Image => 'Image',
            self::Gallery => 'Gallery',
            self::Form => 'Form',
            self::Video => 'Video',
            self::Html => 'HTML',
            self::Columns => 'Columns',
            self::Section => 'Section',
            self::Container => 'Container',
            self::Spacer => 'Spacer',
            self::Separator => 'Separator',
            self::Button => 'Button',
            self::Accordion => 'Accordion',
            self::Tabs => 'Tabs',
            self::CardGrid => 'Card Grid',
            self::SippSchedule => 'SIPP Schedule',
            self::NewsGrid => 'News Grid',
            self::DocumentList => 'Document List',
        };
    }
}
