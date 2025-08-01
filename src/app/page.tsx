"use client";

import React, { useEffect } from 'react';
import { Attachments } from '@/components/ui/Attachments';
import { useNotificationHelpers } from '@/components/ui/notification';
import { ConfirmationPage } from '@/components/ui/ConfirmationPage';
import { MainLayout } from '@/components/layout/MainLayout';
import { useFormField } from '@/hooks/useFormField';
import { useAutoSave } from '@/hooks/useAutoSave';
import { useFileHandling } from '@/hooks/useFileHandling';
import { AutoSaveIndicator, FloatingAutoSaveIndicator } from '@/components/ui/AutoSaveIndicator';
import { FORM_AUTO_SAVE_CONFIGS } from '../../config/features.config';
import Link from 'next/link';

interface UserAgentData {
  userAgent: string;
  ipv4Address: string;
  browserName: string;
  operatingSystem: string;
  timestamp: string;
}

export default function Home() {
  const { showSuccess, showError, showWarning, showInfo } = useNotificationHelpers();

  // 🧪 TESTING CONFIGURATION
  // Set to false for local testing (simulates form submission)
  // Set to true for production PHP backend integration
  const USE_PHP_BACKEND = (() => {
    // Check URL parameter first: ?php=true or ?php=false
    if (typeof window !== 'undefined') {
      const urlParams = new URLSearchParams(window.location.search);
      const phpParam = urlParams.get('php');
      if (phpParam === 'true') return true;
      if (phpParam === 'false') return false;
    }

    // Check environment variable
    const envMode = process.env.NEXT_PUBLIC_USE_PHP_BACKEND;
    if (envMode === 'true') return true;
    if (envMode === 'false') return false;

    // Default: false for local testing, change to true for production
    return false;
  })();

  // Log current mode for debugging
  console.log(`🧪 Form Submission Mode: ${USE_PHP_BACKEND ? 'PHP Backend' : 'Local Testing'}`);

  const nameField = useFormField();
  const emailField = useFormField();
  const phoneField = useFormField();
  const domainNameField = useFormField();
  const subjectField = useFormField();
  const messageField = useFormField();

  // Pre-fill form data once for development mode (allows modification afterward)
  useEffect(() => {
    if (!USE_PHP_BACKEND && !hasPreFilled) {
      const testSubject = "Hosting inquiry for new e-commerce website";
      const testMessage = "I'm interested in your hosting plans for a new e-commerce website. I need information about SSL certificates, bandwidth limits, backup options, and pricing for high-traffic sites. Please also include details about your customer support and uptime guarantees.";

      nameField.setValue("John Smith");
      emailField.setValue("john.smith@example.com");
      phoneField.setValue("(555) 123-4567");
      domainNameField.setValue("example.com");
      subjectField.setValue(testSubject);
      messageField.setValue(testMessage);

      // Update character counts to match pre-filled content
      setSubjectCharacterCount(testSubject.length);
      setCharacterCount(testMessage.length);

      setHasPreFilled(true);
    }
    // Only run when USE_PHP_BACKEND changes or component mounts
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [USE_PHP_BACKEND]);

  const {
    uploadedFiles,
    filePreviews,
    fileDescriptions,
    isDragging,
    handleFileChange,
    handleDrop,
    handleDragEnter,
    handleDragLeave,
    handleDragOver,
    setUploadedFiles,
    setFilePreviews,
    setFileDescriptions,
    processFiles,
    removeFile,
    removeAllFiles
  } = useFileHandling({ showError, showWarning });

  const [isSubmitting, setIsSubmitting] = React.useState<boolean>(false);
  const [submitted, setSubmitted] = React.useState<boolean>(false);
  const [referenceId, setReferenceId] = React.useState<string>("");
  const [characterCount, setCharacterCount] = React.useState<number>(0);
  const [subjectCharacterCount, setSubjectCharacterCount] = React.useState<number>(0);
  const [showAttachment, setShowAttachment] = React.useState<boolean>(false);
  const [hasPreFilled, setHasPreFilled] = React.useState<boolean>(false);

  const [userAgentData, setUserAgentData] = React.useState<UserAgentData>({
    userAgent: '',
    ipv4Address: '',
    browserName: '',
    operatingSystem: '',
    timestamp: ''
  });

  // Enhanced Auto-Save with Global Context
  const autoSaveData = {
    name: nameField.value,
    email: emailField.value,
    phone: phoneField.value,
    domainName: domainNameField.value,
    subject: subjectField.value,
    message: messageField.value,
    showAttachment,
    fileDescriptions
  };

  const {
    status: autoSaveStatus,
    clear: clearAutoSavedData,
  } = useAutoSave(autoSaveData, {
    formType: 'contact-form',
    fields: ['name', 'email', 'phone', 'domainName', 'subject', 'message', 'showAttachment', 'fileDescriptions'],
    onSave: () => {
      // Optional: Show a subtle indication that data was saved
    },
    onError: (error) => {
      console.warn('Auto-save failed:', error);
    }
  });



  // User-Agent and IPv4 detection
  useEffect(() => {
    function parseUserAgent(ua: string) {
      let browserName = "Unknown";
      let operatingSystem = "Unknown";

      if (/chrome|crios/i.test(ua) && !/edge|edg|opr|opera/i.test(ua)) {
        browserName = "Chrome";
      } else if (/firefox|fxios/i.test(ua)) {
        browserName = "Firefox";
      } else if (/safari/i.test(ua) && !/chrome|crios|android/i.test(ua)) {
        browserName = "Safari";
      } else if (/edg/i.test(ua)) {
        browserName = "Edge";
      } else if (/opr|opera/i.test(ua)) {
        browserName = "Opera";
      } else if (/msie|trident/i.test(ua)) {
        browserName = "Internet Explorer";
      }

      if (/windows nt/i.test(ua)) {
        operatingSystem = "Windows";
      } else if (/macintosh|mac os x/i.test(ua)) {
        operatingSystem = "macOS";
      } else if (/android/i.test(ua)) {
        operatingSystem = "Android";
      } else if (/iphone|ipad|ipod/i.test(ua)) {
        operatingSystem = "iOS";
      } else if (/linux/i.test(ua)) {
        operatingSystem = "Linux";
      }

      return { browserName, operatingSystem };
    }

    async function fetchIPv4() {
      try {
        const res = await fetch("https://api.ipify.org?format=json");
        if (!res.ok) throw new Error("Failed to fetch IP");
        const data = await res.json();
        return data.ip || "";
      } catch {
        return "";
      }
    }

    const ua = typeof window !== "undefined" ? window.navigator.userAgent : "";
    const { browserName, operatingSystem } = parseUserAgent(ua);

    fetchIPv4().then(ipv4 => {
      setUserAgentData({
        userAgent: ua,
        ipv4Address: ipv4,
        browserName,
        operatingSystem,
        timestamp: new Date().toISOString()
      });
    });
  }, []);

  // Auto-save functionality
  useEffect(() => {
    const loadSavedData = () => {
      try {
        const savedData = localStorage.getItem('glowhost-form-autosave');
        if (savedData) {
          const parsedData = JSON.parse(savedData);
          nameField.setValue(parsedData.formData.name || '');
          emailField.setValue(parsedData.formData.email || '');
          phoneField.setValue(parsedData.formData.phone || '');
          domainNameField.setValue(parsedData.formData.domainName || '');
          subjectField.setValue(parsedData.formData.subject || '');
          messageField.setValue(parsedData.formData.message || '');
          setShowAttachment(parsedData.showAttachment || false);
          setFileDescriptions(parsedData.fileDescriptions || []);
        }
      } catch (error) {
        console.warn('Failed to load auto-saved data:', error);
        localStorage.removeItem('glowhost-form-autosave');
      }
    };

    loadSavedData();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>): void => {
    const { name, value } = e.target as HTMLInputElement;



    if (name === 'name') nameField.onChange(e);
    if (name === 'email') emailField.onChange(e);
    if (name === 'phone') phoneField.onChange(e);
    if (name === 'domainName') domainNameField.onChange(e);
    if (name === 'subject') subjectField.onChange(e);
    if (name === 'message') messageField.onChange(e);
    if (name === 'message') setCharacterCount(value.length);
    if (name === 'subject') setSubjectCharacterCount(value.length);
  };

  const isFormValid = () => {
    return nameField.value.trim() && emailField.value.trim() && subjectField.value.trim() && messageField.value.trim();
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!isFormValid()) return;

    setIsSubmitting(true);

    try {
      const submissionData: Record<string, unknown> = {
        department: "Sales Questions",
        name: nameField.value,
        email: emailField.value,
        phone: phoneField.value,
        domainName: domainNameField.value,
        subject: subjectField.value,
        message: messageField.value,
        userAgentData: userAgentData,
        uploadedFiles: uploadedFiles.map((file: File) => ({
          name: file.name,
          size: file.size,
          type: file.type
        })),
        fileDescriptions: fileDescriptions
      };

      if (USE_PHP_BACKEND) {
        const response = await fetch('/api/submit-form.php', {
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

        if (result.success) {
          setReferenceId(result.reference_id);
          setSubmitted(true);
          clearAutoSavedData();
          showSuccess("Form Submitted", "Your inquiry has been submitted successfully!");
        } else {
          throw new Error(result.error || 'Submission failed');
        }
      } else {
        await new Promise((resolve) => setTimeout(resolve, 1200));
        setReferenceId(`TEST-${Math.floor(Math.random() * 1000000)}`);
        setSubmitted(true);
        clearAutoSavedData();
        showSuccess("Form Submitted (Test Mode)", "Your inquiry has been submitted (simulated, not sent to PHP backend).");
        console.log("🧪 LOCAL TESTING: Data that would be sent to PHP:", submissionData);
      }

    } catch (error: unknown) {
      console.error('Form submission error:', error);
      showError(
        "Submission Failed",
        error instanceof Error ? error.message : "There was a problem submitting your form. Please try again."
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  return (
    <MainLayout>
      {/* Floating Auto-Save Indicator - Always Visible (Configured via UI Component Library) */}
      <FloatingAutoSaveIndicator
        formType={'contact-form'}
        // Optional: Override default configuration
        // config={{ position: 'top-right', showDuration: 5000 }}
      />

      {submitted ? (
        <ConfirmationPage
          referenceId={referenceId}
          customerName={nameField.value}
          email={emailField.value}
          subject={subjectField.value}
          message={messageField.value}
          department="Sales Questions"
          domainName={domainNameField.value}
          submissionTimestamp={userAgentData.timestamp}
          ipv4Address={userAgentData.ipv4Address}
          browserName={userAgentData.browserName}
          operatingSystem={userAgentData.operatingSystem}
          userAgent={userAgentData.userAgent}
        />
      ) : (
        <div className="max-w-4xl mx-auto">
          <div className="mb-6">
            <h2 className="text-2xl font-bold text-gray-800">Contact GlowHost Sales: New Inquiry</h2>
          </div>



          {/* Test Mode Notification - Simplified */}
          {!USE_PHP_BACKEND && (
            <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
              <div className="flex items-center">
                <div className="flex-shrink-0">
                  <svg className="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div className="ml-3">
                  <h3 className="text-sm font-medium text-blue-800">
                    Development Mode Active
                  </h3>
                  <div className="mt-1 text-sm text-blue-700">
                    <ul className="list-disc list-inside space-y-1">
                      <li>Form pre-filled with test data (fully editable for auto-save testing)</li>
                      <li>All submissions are simulated (not sent to backend)</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          )}

          {/* Form */}
          <div className="rounded-xl border bg-card text-card-foreground shadow-lg">
            <div className="p-6">
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Department */}
                <div className="space-y-2">
                  <label className="text-sm font-medium text-gray-700">Department</label>
                  <input
                    type="text"
                    value="Sales Questions"
                    className="w-full p-3 border rounded-lg bg-gray-100"
                    readOnly
                  />
                </div>

                {/* Name and Email */}
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label htmlFor="name" className="text-sm font-medium text-gray-700">
                      Full Name
                    </label>
                    <input
                      type="text"
                      name="name"
                      value={nameField.value}
                      onChange={handleInputChange}
                      placeholder="Enter your full name"
                      className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <label htmlFor="email" className="text-sm font-medium text-gray-700">
                      Email Address
                    </label>
                    <input
                      type="email"
                      name="email"
                      value={emailField.value}
                      onChange={handleInputChange}
                      placeholder="Enter your email address"
                      className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                      required
                    />
                  </div>
                </div>

                {/* Subject */}
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <label htmlFor="subject" className="text-sm font-medium text-gray-700">
                      Subject
                    </label>
                    <span className="text-xs text-gray-500">{subjectCharacterCount}/250 characters</span>
                  </div>
                  <input
                    type="text"
                    name="subject"
                    value={subjectField.value}
                    onChange={handleInputChange}
                    placeholder="Brief description of your inquiry"
                    className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                    maxLength={250}
                    required
                  />
                </div>

                {/* Phone and Domain */}
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label htmlFor="phone" className="text-sm font-medium text-gray-700">
                      Phone Number <span className="text-gray-500 font-normal">(Optional)</span>
                    </label>
                    <input
                      type="tel"
                      name="phone"
                      value={phoneField.value}
                      onChange={handleInputChange}
                      placeholder="Enter your phone number"
                      className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                    />
                  </div>
                  <div className="space-y-2">
                    <label htmlFor="domainName" className="text-sm font-medium text-gray-700">
                      Domain Name <span className="text-gray-500 font-normal">(Optional)</span>
                    </label>
                    <input
                      type="text"
                      name="domainName"
                      value={domainNameField.value}
                      onChange={handleInputChange}
                      placeholder="example.com"
                      className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                    />
                  </div>
                </div>

                {/* Message */}
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <label htmlFor="message" className="text-sm font-medium text-gray-700">
                      Message
                    </label>
                    <span className="text-xs text-gray-500">{characterCount}/10000 characters</span>
                  </div>
                  <textarea
                    name="message"
                    value={messageField.value}
                    onChange={handleInputChange}
                    placeholder="Please provide details about your hosting needs, questions, or requirements..."
                    className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                    rows={6}
                    maxLength={10000}
                    required
                  />
                </div>

                {/* Attachments Section */}
                <div className="space-y-4">
                  <div className="flex items-center">
                    <h3 className="text-lg font-semibold text-gray-800">Attachments</h3>
                    <span className="text-gray-500 font-normal text-sm ml-2">(Optional)</span>
                  </div>

                  {!showAttachment ? (
                    <button
                      type="button"
                      onClick={() => setShowAttachment(true)}
                      className="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group"
                    >
                      <div className="flex items-center justify-center space-x-2 text-gray-600 group-hover:text-blue-600">
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span className="font-medium">Add Files</span>
                      </div>
                    </button>
                  ) : (
                    <Attachments
                      uploadedFiles={uploadedFiles}
                      filePreviews={filePreviews}
                      fileDescriptions={fileDescriptions}
                      onFileChange={handleFileChange}
                      onDrop={handleDrop}
                      onDragEnter={handleDragEnter}
                      onDragLeave={handleDragLeave}
                      onDragOver={handleDragOver}
                      isDragging={isDragging}
                      setFileDescriptions={setFileDescriptions}
                      removeFile={removeFile}
                      removeAllFiles={removeAllFiles}
                    />
                  )}
                </div>

                {/* Submit Button */}
                <div className="flex justify-center pt-6">
                  <button
                    type="submit"
                    disabled={!isFormValid() || isSubmitting}
                    className="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white px-8 py-4 text-lg font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-md disabled:bg-gray-400"
                  >
                    {isSubmitting ? "Submitting..." : "Submit Your Inquiry"}
                  </button>
                </div>

                <div className="flex justify-center pt-2">
                  <p className="text-sm text-gray-500">
                    {isFormValid() ? "Ready to submit" : "Please complete all required fields to submit"}
                  </p>
                </div>
              </form>
            </div>
          </div>
        </div>
      )}
    </MainLayout>
  );
}
