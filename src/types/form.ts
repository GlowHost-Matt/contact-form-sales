export interface FormData {
  department: string;
  name: string;
  email: string;
  phone: string;
  domainName: string;
  subject: string;
  message: string;
  uploadedFiles: File[];
  filePreviews: string[];
  fileDescriptions: string[];
}

export interface ReplyFormData {
  message: string;
  uploadedFiles: File[];
  filePreviews: string[];
  fileDescriptions: string[];
}

export interface FormValidation {
  isValid: boolean;
  errors: Record<string, string>;
}

export interface AutoSaveStatus {
  status: 'idle' | 'saving' | 'saved';
  lastSaved?: Date;
}
