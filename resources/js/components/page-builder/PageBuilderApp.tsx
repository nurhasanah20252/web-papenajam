import React from 'react';
import ReactDOM from 'react-dom/client';
import PageBuilder from './PageBuilder';
import { Page } from './types';

document.addEventListener('DOMContentLoaded', () => {
  const element = document.getElementById('page-builder-app');
  if (!element) return;

  const pageData = (element as HTMLElement).dataset.page;
  if (!pageData) return;

  const page: Page = JSON.parse(pageData);

  ReactDOM.createRoot(element).render(
    <React.StrictMode>
      <PageBuilder
        page={page}
        onCancel={() => {
          window.location.href = `/admin/pages/${page.id}/edit`;
        }}
        onSave={() => {
          console.log('Page saved successfully');
        }}
      />
    </React.StrictMode>
  );
});
