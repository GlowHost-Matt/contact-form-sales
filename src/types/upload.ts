export interface FileUploadProgress {
  fileName: string;
  progress: number;
  status: 'uploading' | 'completed' | 'failed';
  startTime: number;
}

export interface FilePreviewModal {
  isOpen: boolean;
  file: {
    name: string;
    type: string;
    size: string;
    description: string;
    url?: string;
  } | null;
}

export interface ProcessedFile {
  file: File;
  preview: string;
  description: string;
  isValid: boolean;
  error?: string;
}

export interface FileValidationConfig {
  maxFileSize: number; // in bytes
  allowedImageExtensions: string[];
  allowedDocExtensions: string[];
  allowedArchiveExtensions: string[];
}

export interface DragDropState {
  isDragging: boolean;
  dragCounter: number;
}
