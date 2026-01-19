import { useState, useCallback } from 'react';
import axios from 'axios';
import { PageBuilderContent } from '@/components/page-builder/types';

export function usePageBuilder() {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const savePageBuilder = useCallback(async (pageId: number, content: PageBuilderContent) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await axios.put(`/builder/pages/${pageId}`, content);
      return response.data;
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Failed to save page';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const duplicateBlock = useCallback(async (pageId: number, blockId: string) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await axios.post(`/builder/pages/${pageId}/blocks/duplicate`, {
        block_id: blockId,
      });
      return response.data;
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Failed to duplicate block';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const deleteBlock = useCallback(async (pageId: number, blockId: string) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await axios.delete(`/builder/pages/${pageId}/blocks/delete`, {
        data: { block_id: blockId },
      });
      return response.data;
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Failed to delete block';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  }, []);

  const previewPage = useCallback(async (pageId: number, content: PageBuilderContent) => {
    setIsLoading(true);
    setError(null);

    try {
      const response = await axios.post(`/builder/pages/${pageId}/preview`, content);
      return response.data;
    } catch (err: any) {
      const errorMessage = err.response?.data?.message || 'Failed to preview page';
      setError(errorMessage);
      throw new Error(errorMessage);
    } finally {
      setIsLoading(false);
    }
  }, []);

  return {
    savePageBuilder,
    duplicateBlock,
    deleteBlock,
    previewPage,
    isLoading,
    error,
  };
}
