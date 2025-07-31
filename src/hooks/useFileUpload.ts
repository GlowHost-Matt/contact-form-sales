import { useState, useCallback } from 'react';
import type { DragDropState, ProcessedFile } from '@/types';
import { FILE_UPLOAD_CONFIG } from '../../config/features.config';
import { useNotificationHelpers } from '@/components/ui/notification';

export function useFileUpload() {
  const [dragState, setDragState] = useState<DragDropState>({
    isDragging: false,
    dragCounter: 0
  });

  const { showError, showWarning } = useNotificationHelpers();

  // Format file size for display
  const formatFileSize = useCallback((bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  }, []);

  // Validate a single file
  const validateFile = useCallback((file: File): { isValid: boolean; error?: string } => {
    // Check file size
    if (file.size > FILE_UPLOAD_CONFIG.maxFileSize) {
      return {
        isValid: false,
        error: FILE_UPLOAD_CONFIG.messages.fileTooLarge(
          file.name,
          formatFileSize(FILE_UPLOAD_CONFIG.maxFileSize)
        )
      };
    }

    // Basic file validation
    const fileName = file.name.toLowerCase();
    const fileExtension = fileName.substring(fileName.lastIndexOf('.'));

    // Check if it's a valid file type
    const isValidType = [
      ...FILE_UPLOAD_CONFIG.allowedExtensions.images,
      ...FILE_UPLOAD_CONFIG.allowedExtensions.documents,
      ...FILE_UPLOAD_CONFIG.allowedExtensions.archives
    ].includes(fileExtension as any);

    if (!isValidType) {
      return {
        isValid: false,
        error: FILE_UPLOAD_CONFIG.messages.invalidFileType(file.name)
      };
    }

    return { isValid: true };
  }, [formatFileSize]);

  // Process multiple files
  const processFiles = useCallback(async (files: File[]): Promise<ProcessedFile[]> => {
    const processedFiles: ProcessedFile[] = [];

    for (const file of files) {
      const validation = validateFile(file);

      if (!validation.isValid) {
        showError("File Error", validation.error!);
        continue;
      }

      let preview = '';

      // Create preview for images
      if (file.type.startsWith('image/')) {
        try {
          preview = await new Promise<string>((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target?.result as string || '');
            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsDataURL(file);
          });
        } catch (error) {
          console.error('Error creating preview:', error);
        }
      }

      processedFiles.push({
        file,
        preview,
        description: '',
        isValid: true
      });
    }

    return processedFiles;
  }, [validateFile, showError]);

  // Drag and drop handlers
  const handleDragEnter = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragState(prev => ({
      isDragging: true,
      dragCounter: prev.dragCounter + 1
    }));
  }, []);

  const handleDragLeave = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragState(prev => {
      const newCounter = prev.dragCounter - 1;
      return {
        isDragging: newCounter > 0,
        dragCounter: newCounter
      };
    });
  }, []);

  const handleDragOver = useCallback((e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
  }, []);

  const handleDrop = useCallback(async (e: React.DragEvent): Promise<ProcessedFile[]> => {
    e.preventDefault();
    e.stopPropagation();

    setDragState({ isDragging: false, dragCounter: 0 });

    const files = Array.from(e.dataTransfer.files);
    return await processFiles(files);
  }, [processFiles]);

  // File input change handler
  const handleFileChange = useCallback(async (e: React.ChangeEvent<HTMLInputElement>): Promise<ProcessedFile[]> => {
    if (!e.target.files) return [];

    const files = Array.from(e.target.files);
    const processed = await processFiles(files);

    // Clear the input
    e.target.value = '';

    return processed;
  }, [processFiles]);

  return {
    dragState,
    formatFileSize,
    validateFile,
    processFiles,
    handleDragEnter,
    handleDragLeave,
    handleDragOver,
    handleDrop,
    handleFileChange
  };
}
