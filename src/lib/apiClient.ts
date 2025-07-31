import type { SubmissionData, ApiResponse, ReplySubmissionData, UserAgentData, FormData } from '@/types';
import { API_CONFIG } from '../../config/app.config';

/**
 * Submit main contact form
 */
export async function submitContactForm(
  formData: FormData,
  userAgentData: UserAgentData
): Promise<ApiResponse> {
  const submissionData: SubmissionData = {
    // Form fields
    department: formData.department,
    name: formData.name,
    email: formData.email,
    phone: formData.phone,
    domainName: formData.domainName,
    subject: formData.subject,
    message: formData.message,

    // User agent data
    userAgentData: userAgentData,

    // File information (for logging/tracking)
    uploadedFiles: formData.uploadedFiles.map(file => ({
      name: file.name,
      size: file.size,
      type: file.type
    })),
    fileDescriptions: formData.fileDescriptions
  };

  const response = await fetch(API_CONFIG.endpoints.SUBMIT_FORM, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(submissionData)
  });

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const result = await response.json();

  if (!result.success) {
    throw new Error(result.error || 'Submission failed');
  }

  return result;
}

/**
 * Submit reply to support thread
 */
export async function submitReply(
  message: string,
  referenceId: string,
  uploadedFiles: File[] = [],
  fileDescriptions: string[] = []
): Promise<ApiResponse> {
  const submissionData: ReplySubmissionData = {
    message,
    referenceId,
    uploadedFiles: uploadedFiles.map(file => ({
      name: file.name,
      size: file.size,
      type: file.type
    })),
    fileDescriptions
  };

  // For now, simulate API call since we don't have a reply endpoint
  await new Promise(resolve => setTimeout(resolve, 1500));

  return {
    success: true,
    message: 'Reply submitted successfully'
  };
}

/**
 * Test PHP backend connectivity
 */
export async function testPhpBackend(): Promise<boolean> {
  try {
    const response = await fetch(API_CONFIG.endpoints.SUBMIT_FORM); // Using submit form endpoint for testing
    const result = await response.json();
    return result.status === 'PHP is working';
  } catch {
    return false;
  }
}

/**
 * Simulate form submission for local testing
 */
export async function simulateSubmission(
  formData: FormData,
  userAgentData: UserAgentData
): Promise<ApiResponse> {
  // Simulate network delay
  await new Promise(resolve => setTimeout(resolve, 1200));

  // Generate test reference ID
  const referenceId = `TEST-${Math.floor(Math.random() * 1000000)}`;

  // Log what would have been sent for debugging
  console.log("🧪 LOCAL TESTING: Data that would be sent to PHP:", {
    formData,
    userAgentData
  });

  return {
    success: true,
    reference_id: referenceId,
    message: 'Form submitted successfully (simulated)'
  };
}
