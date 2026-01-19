import React from 'react';
import PageBuilder from '@/components/page-builder/PageBuilder';
import { Page } from '@/components/page-builder/types';
import { Head, router } from '@inertiajs/react';

interface EditorProps {
  page: Page;
  versions: any[];
  templates: any[];
  block_types: any[];
}

export default function Editor({ page, versions, templates, block_types }: EditorProps) {
  const handleSave = (content: any) => {
    // Save is handled within the PageBuilder component via usePageBuilder hook,
    // but we can add additional logic here if needed.
    console.log('Page content updated', content);
  };

  const handleCancel = () => {
    router.get(`/admin/pages/${page.id}/edit`);
  };

  return (
    <>
      <Head title={`Editing: ${page.title}`} />
      <div className="fixed inset-0 z-50 bg-white">
        <PageBuilder
          page={page}
          onSave={handleSave}
          onCancel={handleCancel}
        />
      </div>
    </>
  );
}
