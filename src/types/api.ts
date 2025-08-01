export interface UserAgentData {
  userAgent: string;
  ipv4Address: string;
  browserName: string;
  operatingSystem: string;
  timestamp: string;
}

export interface SubmissionData {
  // Form fields
  department: string;
  name: string;
  email: string;
  phone: string;
  domainName: string;
  subject: string;
  message: string;

  // User agent data
  userAgentData: UserAgentData;

  // File information
  uploadedFiles: {
    name: string;
    size: number;
    type: string;
  }[];
  fileDescriptions: string[];
}

export interface ApiResponse {
  success: boolean;
  reference_id?: string;
  error?: string;
  message?: string;
}

export interface ReplySubmissionData {
  message: string;
  referenceId: string;
  uploadedFiles: {
    name: string;
    size: number;
    type: string;
  }[];
  fileDescriptions: string[];
}
