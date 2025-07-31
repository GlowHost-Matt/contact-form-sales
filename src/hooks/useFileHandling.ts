import { useState } from 'react';

type NotificationCallback = (title: string, message: string) => void;

interface FileHandlingOptions {
  showError?: NotificationCallback;
  showWarning?: NotificationCallback;
}

export const useFileHandling = (options: FileHandlingOptions = {}) => {
  const { showError, showWarning } = options;

  const [uploadedFiles, setUploadedFiles] = useState<File[]>([]);
  const [filePreviews, setFilePreviews] = useState<string[]>([]);
  const [fileDescriptions, setFileDescriptions] = useState<string[]>([]);
  const [isDragging, setIsDragging] = useState(false);

  const processFiles = async (files: File[]): Promise<void> => {
    const maxFileSize = 10 * 1024 * 1024; // 10MB
    const validFiles: File[] = [];
    const newPreviews: string[] = [];

    for (const file of files) {
      if (file.size > maxFileSize) {
        const message = `"${file.name}" exceeds the 10MB limit. Please choose a smaller file.`;
        if (showError) {
          showError("File Too Large", message);
        } else {
          console.error(message);
        }
        continue;
      }

      const fileName = file.name.toLowerCase();
      const fileExtension = fileName.substring(fileName.lastIndexOf('.'));

      const allowedImageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'];
      const allowedDocExtensions = ['.pdf', '.txt', '.log'];
      const allowedArchiveExtensions = ['.zip', '.rar', '.7z'];

      let isValidFile = false;

      if (
        allowedImageExtensions.includes(fileExtension) ||
        allowedDocExtensions.includes(fileExtension) ||
        allowedArchiveExtensions.includes(fileExtension)
      ) {
        isValidFile = true;
      }

      if (!isValidFile) {
        const message = `"${file.name}" is not a supported file type. Please upload images, PDFs, text files (.txt, .log), or safe archives (.zip, .rar, .7z).`;
        if (showWarning) {
          showWarning("File Type Not Allowed", message);
        } else {
          console.warn(message);
        }
        continue;
      }

      validFiles.push(file);

      if (file.type.startsWith('image/')) {
        try {
          const preview = await new Promise<string>((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e: ProgressEvent<FileReader>) => {
              resolve((e.target?.result as string) || '');
            };
            reader.onerror = () => reject(new Error('Failed to read file'));
            reader.readAsDataURL(file);
          });
          newPreviews.push(preview);
        } catch (error) {
          console.error('Error creating preview:', error);
          newPreviews.push('');
        }
      } else {
        newPreviews.push('');
      }
    }

    if (validFiles.length > 0) {
      const newDescriptions = new Array(validFiles.length).fill('');
      setUploadedFiles((prev: File[]) => [...prev, ...validFiles]);
      setFilePreviews((prev: string[]) => [...prev, ...newPreviews]);
      setFileDescriptions((prev: string[]) => [...prev, ...newDescriptions]);
    }
  };

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>): Promise<void> => {
    if (e.target.files) {
      const files = Array.from(e.target.files);
      await processFiles(files);
      e.target.value = '';
    }
  };

  const handleDrop = async (e: React.DragEvent<HTMLDivElement>): Promise<void> => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);

    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
      await processFiles(files);
    }
  };

  const handleDragEnter = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  };

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    e.stopPropagation();
  };

  const removeAllFiles = () => {
    setUploadedFiles([]);
    setFilePreviews([]);
    setFileDescriptions([]);
  };

  const removeFile = (index: number) => {
    setUploadedFiles((prev: File[]) => prev.filter((_: File, i: number) => i !== index));
    setFilePreviews((prev: string[]) => prev.filter((_: string, i: number) => i !== index));
    setFileDescriptions((prev: string[]) => prev.filter((_: string, i: number) => i !== index));
  };

  return {
    uploadedFiles,
    setUploadedFiles,
    filePreviews,
    setFilePreviews,
    fileDescriptions,
    setFileDescriptions,
    isDragging,
    handleFileChange,
    handleDrop,
    handleDragEnter,
    handleDragLeave,
    handleDragOver,
    processFiles,
    removeAllFiles,
    removeFile
  };
};
