"use client";

import { useState, useEffect } from "react";
import { useNotificationHelpers } from "@/components/ui/notification";
import { Breadcrumb, getBreadcrumbConfig, getAvailableDepartments } from "@/components/ui/breadcrumb";

interface FormData {
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

interface FileUploadProgress {
  fileName: string;
  progress: number;
  status: 'uploading' | 'completed' | 'failed';
  startTime: number;
}

interface UserAgentData {
  userAgent: string;
  ipv4Address: string;
  browserName: string;
  operatingSystem: string;
  timestamp: string;
}

export default function Home() {
  const { showSuccess, showError, showWarning, showInfo } = useNotificationHelpers();

  // 🔧 ADMIN-CONTROLLED CONFIGURATION
  // Configuration is now controlled via Admin Settings instead of URL parameters
  const getAdminSettings = () => {
    if (typeof window !== 'undefined') {
      try {
        const savedSettings = localStorage.getItem('mockAdminSettings');
        if (savedSettings) {
          return JSON.parse(savedSettings);
        }
      } catch (error) {
        console.warn('Failed to load admin settings:', error);
      }
    }

    // Default settings for when admin hasn't configured anything
    return {
      formTestingMode: 'testing', // 'production' or 'testing' - React-only mode (PHP is secondary)
      enableTestData: false, // Clean form, no test data
      enableDebugMode: false, // Clean production-ready mode
      testDataScenario: 'random',
      defaultDepartment: 'General Inquiry'
    };
  };

  const adminSettings = getAdminSettings();
  const USE_PHP_BACKEND = adminSettings.formTestingMode === 'production';
  const ENABLE_TEST_DATA = adminSettings.enableTestData;
  const ENABLE_DEBUG_MODE = adminSettings.enableDebugMode;
  const SHOW_DEPARTMENT_DROPDOWN = adminSettings.formTestingMode === 'testing';

  // Log current mode for debugging (only if debug mode enabled)
  if (ENABLE_DEBUG_MODE) {
    console.log(`🔧 Admin Settings:`, adminSettings);
    console.log(`🧪 Form Submission Mode: ${USE_PHP_BACKEND ? 'PHP Backend' : 'Local Testing'}`);
    console.log(`📝 Test Data Enabled: ${ENABLE_TEST_DATA}`);
  }

  // 🧪 COMPREHENSIVE TEST DATA FOR REALISTIC TESTING
  const getTestData = () => {
    if (!ENABLE_TEST_DATA) {
      return {
        name: "",
        email: "",
        phone: "",
        domainName: "",
        subject: "",
        message: ""
      };
    }

    // Realistic test scenarios for comprehensive testing
    const testScenarios = {
      sarah: {
        name: "Sarah Mitchell",
        email: "sarah.mitchell@techstartup.io",
        phone: "(555) 234-7890",
        domainName: "techstartup.io",
        subject: "Enterprise hosting solution for high-traffic SaaS platform",
        message: "Hi there,\n\nWe're a fast-growing SaaS company expecting 50,000+ daily active users within the next 6 months. We need:\n\n• Dedicated servers with 99.9% uptime SLA\n• Auto-scaling capabilities for traffic spikes\n• Database optimization and backup solutions\n• SSL certificates and security hardening\n• 24/7 technical support with <2hr response time\n• CDN integration for global performance\n\nCurrent infrastructure costs are $2,500/month. Looking for a hosting partner that can grow with us. Can you provide a detailed quote and technical specifications?\n\nBest regards,\nSarah Mitchell\nCTO, TechStartup Inc."
      },
      marcus: {
        name: "Marcus Rodriguez",
        email: "marcus@ecommercepro.com",
        phone: "(555) 345-8901",
        domainName: "ecommercepro.com",
        subject: "WooCommerce hosting with PCI compliance requirements",
        message: "Hello GlowHost team,\n\nI'm launching an e-commerce store selling premium electronics and need hosting that meets PCI DSS compliance standards. The site will use WooCommerce with these requirements:\n\n• PCI-compliant hosting environment\n• SSL certificates and payment gateway integration\n• Regular security scanning and monitoring\n• Automated daily backups with 30-day retention\n• Expected traffic: 10,000 visitors/month initially\n• Storage needs: ~500 products with high-res images\n\nAlso need staging environment for testing updates before going live. What hosting plans would you recommend, and what's the setup timeline?\n\nThanks,\nMarcus Rodriguez"
      },
      jennifer: {
        name: "Jennifer Chen",
        email: "jen.chen@creativestudio.design",
        phone: "(555) 456-9012",
        domainName: "creativestudio.design",
        subject: "WordPress hosting for creative agency portfolio site",
        message: "Hi,\n\nOur creative agency needs reliable WordPress hosting for our portfolio website. We showcase large image galleries and video content for clients, so performance is crucial.\n\nRequirements:\n• WordPress-optimized hosting\n• Fast loading times for image/video content\n• Easy staging environment setup\n• Regular backups and security updates\n• Support for custom themes and plugins\n• Expected traffic: 5,000 visitors/month\n\nWe also manage websites for 15+ clients and may need reseller hosting options. Do you offer white-label solutions?\n\nLooking forward to your recommendations!\n\nJennifer Chen\nCreative Director"
      },
      david: {
        name: "David Thompson",
        email: "dthompson@bloggernetwork.net",
        phone: "(555) 567-0123",
        domainName: "bloggernetwork.net",
        subject: "Multi-site hosting solution for blog network",
        message: "Hello,\n\nI run a network of 12 WordPress blogs covering different niches (food, travel, tech, finance) and need a hosting solution that can handle:\n\n• WordPress Multisite installation\n• Shared resources across all blogs\n• Individual domain mapping for each blog\n• Centralized management and updates\n• Traffic varies: 1,000-15,000 visitors/month per blog\n• SEO-friendly hosting with fast page speeds\n\nCurrently with a shared host but experiencing slowdowns during traffic spikes. Need something more robust but still cost-effective.\n\nCan you suggest a suitable hosting plan and pricing for this setup?\n\nBest,\nDavid Thompson"
      }
    };

    // Get admin-selected scenario or use random
    const selectedScenario = adminSettings.testDataScenario || 'random';

    if (selectedScenario === 'random') {
      const scenarioKeys = Object.keys(testScenarios);
      const randomKey = scenarioKeys[Math.floor(Math.random() * scenarioKeys.length)];
      return testScenarios[randomKey as keyof typeof testScenarios];
    } else {
      return testScenarios[selectedScenario as keyof typeof testScenarios] || testScenarios.sarah;
    }
  };

  const testData = getTestData();

  const [formData, setFormData] = useState<FormData>({
    department: SHOW_DEPARTMENT_DROPDOWN
      ? (getAvailableDepartments()[0] || "Sales Questions")
      : (adminSettings.defaultDepartment || "General Inquiry"),
    name: testData.name,
    email: testData.email,
    phone: testData.phone,
    domainName: testData.domainName,
    subject: testData.subject,
    message: testData.message,
    uploadedFiles: [],
    filePreviews: [],
    fileDescriptions: []
  });

  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [referenceId, setReferenceId] = useState("");
  const [characterCount, setCharacterCount] = useState(testData.message.length);
  const [subjectCharacterCount, setSubjectCharacterCount] = useState(testData.subject.length);
  const [showAttachment, setShowAttachment] = useState(false);
  const [isDragging, setIsDragging] = useState(false);
  const [uploadProgress, setUploadProgress] = useState<Map<string, FileUploadProgress>>(new Map());
  const [autoSaveStatus, setAutoSaveStatus] = useState<'idle' | 'saving' | 'saved'>('idle');

  const [userAgentData, setUserAgentData] = useState<UserAgentData>({
    userAgent: '',
    ipv4Address: '',
    browserName: '',
    operatingSystem: '',
    timestamp: ''
  });

  // User-Agent and IPv4 detection
  useEffect(() => {
    // Parse browser and OS from user agent string
    function parseUserAgent(ua: string) {
      let browserName = "Unknown";
      let operatingSystem = "Unknown";

      // Browser detection (very basic)
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

      // OS detection (very basic)
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

    // Get IPv4 address using a public API (simple, not for production privacy)
    async function fetchIPv4() {
      try {
        // Use ipify (returns IPv4 by default)
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
    // Only auto-load saved data if test data is disabled
    // This prevents auto-saved data from overriding admin-controlled test data
    const loadSavedData = () => {
      // If test data is enabled, don't override it with auto-saved data
      if (ENABLE_TEST_DATA) {
        if (ENABLE_DEBUG_MODE) {
          console.log('🧪 Test data enabled, skipping auto-saved data load');
        }
        return;
      }

      try {
        const savedData = localStorage.getItem('glowhost-form-autosave');
        if (savedData) {
          const parsedData = JSON.parse(savedData);
          setFormData(prev => ({ ...prev, ...parsedData.formData }));
          setCharacterCount(parsedData.formData.message?.length || 0);
          setSubjectCharacterCount(parsedData.formData.subject?.length || 0);
          setShowAttachment(parsedData.showAttachment || false);

          if (ENABLE_DEBUG_MODE) {
            console.log('📂 Loaded auto-saved form data');
          }
        }
      } catch (error) {
        console.warn('Failed to load auto-saved data:', error);
        localStorage.removeItem('glowhost-form-autosave');
      }
    };

    loadSavedData();
  }, [ENABLE_TEST_DATA, ENABLE_DEBUG_MODE]);

  // Debounced auto-save function
  useEffect(() => {
    // Don't auto-save when test data is enabled to avoid overriding test scenarios
    if (ENABLE_TEST_DATA) {
      if (ENABLE_DEBUG_MODE) {
        console.log('🧪 Test data enabled, skipping auto-save');
      }
      return;
    }
    const saveToLocalStorage = () => {
      try {
        setAutoSaveStatus('saving');
        const dataToSave = {
          formData: {
            name: formData.name,
            email: formData.email,
            phone: formData.phone,
            domainName: formData.domainName,
            subject: formData.subject,
            message: formData.message
          },
          showAttachment,
          timestamp: Date.now()
        };

        localStorage.setItem('glowhost-form-autosave', JSON.stringify(dataToSave));
        setAutoSaveStatus('saved');

        // Reset status after 2 seconds
        setTimeout(() => setAutoSaveStatus('idle'), 2000);
      } catch (error) {
        console.warn('Failed to auto-save form data:', error);
        setAutoSaveStatus('idle');
      }
    };

    // Only auto-save if there's meaningful data
    const hasData = formData.name || formData.email || formData.phone ||
                   formData.domainName || formData.subject || formData.message;

    if (hasData) {
      const timeoutId = setTimeout(saveToLocalStorage, 1000); // Debounce for 1 second
      return () => clearTimeout(timeoutId);
    }
  }, [formData, showAttachment, ENABLE_TEST_DATA, ENABLE_DEBUG_MODE]);

  // Clear auto-saved data on successful submission
  const clearAutoSavedData = () => {
    try {
      localStorage.removeItem('glowhost-form-autosave');
      setAutoSaveStatus('idle');
    } catch (error) {
      console.warn('Failed to clear auto-saved data:', error);
    }
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => {
    const { name, value } = e.target;
    setFormData(prev => ({ ...prev, [name]: value }));
    if (name === 'message') setCharacterCount(value.length);
    if (name === 'subject') setSubjectCharacterCount(value.length);
  };

  const isFormValid = () => {
    return formData.name.trim() && formData.email.trim() && formData.subject.trim() && formData.message.trim();
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!isFormValid()) return;

    setIsSubmitting(true);

    try {
      // Prepare form data for PHP endpoint
      const submissionData = {
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

      if (USE_PHP_BACKEND) {
        // Submit to PHP endpoint
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
          // Use the reference ID from PHP response
          setReferenceId(result.reference_id);
          setSubmitted(true);
          clearAutoSavedData(); // Clear auto-saved data on successful submission
          showSuccess("Form Submitted", "Your inquiry has been submitted successfully!");
        } else {
          throw new Error(result.error || 'Submission failed');
        }
      } else {
        // 🧪 LOCAL TESTING MODE: Simulate a successful submission
        await new Promise((resolve) => setTimeout(resolve, 1200));
        setReferenceId(`TEST-${Math.floor(Math.random() * 1000000)}`);
        setSubmitted(true);
        clearAutoSavedData();
        showSuccess("Form Submitted (Test Mode)", "Your inquiry has been submitted (simulated, not sent to PHP backend).");

        if (ENABLE_DEBUG_MODE) {
          console.log("🧪 LOCAL TESTING: Data that would be sent to PHP:", submissionData);
        }
      }

    } catch (error) {
      console.error('Form submission error:', error);
      showError(
        "Submission Failed",
        error instanceof Error ? error.message : "There was a problem submitting your form. Please try again."
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  // Helper functions for file processing (continuing in next edit)
  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  if (submitted) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">
        <header className="bg-[#1a679f] text-white">
          <div className="container mx-auto px-4 py-6">
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <img
                  src="https://glowhost.com/wp-content/uploads/page_notag.png"
                  alt="GlowHost"
                  className="h-8 w-auto object-contain sm:h-10 md:h-12"
                />
              </div>
              <div className="text-right text-sm">
                <div className="text-cyan-200">24 / 7 / 365 Support</div>
                <div className="font-semibold">Toll Free Sales 1 (888) 293-HOST</div>
              </div>
            </div>
          </div>
        </header>

        <main className="container mx-auto px-4 py-8">
          <div className="max-w-4xl mx-auto">
            <div className="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
              <div className="mb-4 flex justify-center">
                <img
                  src="https://glowhost.com/wp-content/uploads/glow-hero.webp"
                  alt="GlowHost Hero"
                  className="w-24 h-24 sm:w-32 sm:h-32 md:w-40 md:h-40 object-contain"
                />
              </div>
              <h1 className="text-2xl font-bold text-green-800 mb-2">Thank You!</h1>
              <p className="text-green-700 mb-6">Your sales inquiry has been submitted successfully.</p>
              <div className="bg-white p-4 rounded-lg border">
                <p className="text-sm text-gray-500 mb-2">Reference ID</p>
                <p className="text-xl font-mono font-bold">{referenceId}</p>
                {/* Show testing mode indicator */}
                {!USE_PHP_BACKEND && (
                  <div className="mt-2 text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded">
                    🧪 Simulated in Local Testing Mode
                  </div>
                )}
              </div>

              {/* Modern Thread Display */}
              <div className="bg-white border border-gray-200 rounded-xl p-6 mt-6 shadow-sm text-left">
                <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                  <svg className="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
                  </svg>
                  Support Thread
                </h3>

                {/* Thread Post */}
                <div className="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                  {/* Post Header */}
                  <div className="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center space-x-3">
                        {/* User Avatar */}
                        <div className="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                          {formData.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)}
                        </div>

                        {/* User Info */}
                        <div>
                          <div className="font-semibold text-gray-900">{formData.name}</div>
                          <div className="text-sm text-gray-600">
                            {formData.email}
                            {formData.phone && (
                              <span className="ml-2">• {formData.phone}</span>
                            )}
                          </div>
                        </div>
                      </div>

                      {/* Timestamp */}
                      <div className="text-right">
                        <div className="text-sm text-gray-600">
                          {new Date(userAgentData.timestamp).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                          })}
                        </div>
                        <div className="text-xs text-gray-500">
                          {new Date(userAgentData.timestamp).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit'
                          })}
                        </div>
                      </div>
                    </div>

                    {/* Subject Line */}
                    <div className="mt-3 pt-3 border-t border-blue-200">
                      <div className="flex items-center space-x-2">
                        <span className="text-xs font-medium text-blue-700 bg-blue-200 px-2 py-1 rounded-full">
                          {formData.department}
                        </span>
                        {formData.domainName && (
                          <span className="text-xs font-medium text-gray-600 bg-gray-200 px-2 py-1 rounded-full">
                            {formData.domainName}
                          </span>
                        )}
                      </div>
                      <h4 className="text-base font-semibold text-gray-900 mt-2">
                        {formData.subject}
                      </h4>
                    </div>
                  </div>

                  {/* Post Body */}
                  <div className="p-4">
                    <div className="prose prose-gray max-w-none">
                      <div className="text-gray-800 whitespace-pre-wrap leading-relaxed">
                        {formData.message}
                      </div>
                    </div>

                    {/* File Attachments */}
                    {formData.uploadedFiles.length > 0 && (
                      <div className="mt-4 pt-4 border-t border-gray-200">
                        <div className="text-sm font-medium text-gray-700 mb-2">
                          📎 {formData.uploadedFiles.length} attachment{formData.uploadedFiles.length !== 1 ? 's' : ''}:
                        </div>
                        <div className="space-y-2">
                          {formData.uploadedFiles.map((file, index) => (
                            <div key={index} className="flex items-center space-x-3 bg-white p-2 rounded border border-gray-200">
                              <div className="text-lg">
                                {file.type.startsWith('image/') ? '🖼️' :
                                 file.type.includes('pdf') ? '📄' :
                                 file.type.includes('zip') || file.type.includes('rar') ? '🗜️' : '📎'}
                              </div>
                              <div className="flex-1 min-w-0">
                                <div className="text-sm font-medium text-gray-900 truncate">
                                  {file.name}
                                </div>
                                <div className="text-xs text-gray-500">
                                  {formatFileSize(file.size)} • {file.type || 'Unknown type'}
                                  {formData.fileDescriptions[index] && (
                                    <span className="ml-2">• {formData.fileDescriptions[index]}</span>
                                  )}
                                </div>
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}

                    {/* Post Footer */}
                    <div className="mt-4 pt-4 border-t border-gray-200 flex items-center justify-between text-xs text-gray-500">
                      <div className="flex items-center space-x-4">
                        <span>Reference: {referenceId}</span>
                        <span>•</span>
                        <span>Status: Received</span>
                        {!USE_PHP_BACKEND && (
                          <>
                            <span>•</span>
                            <span className="text-blue-600">🧪 Test Mode</span>
                          </>
                        )}
                      </div>
                      <div className="flex items-center space-x-2">
                        <span>{userAgentData.browserName} on {userAgentData.operatingSystem}</span>
                      </div>
                    </div>
                  </div>
                </div>

                {/* Future Response Area */}
                <div className="mt-4 p-4 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                  <div className="text-center text-gray-500">
                    <div className="text-sm font-medium mb-1">Waiting for GlowHost Support Team Response</div>
                    <div className="text-xs">We'll respond to your inquiry within 24 hours</div>
                  </div>
                </div>
              </div>

              {/* Technical Information */}
              <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-4 text-left">
                <h3 className="text-sm font-semibold text-gray-700 mb-3">Technical Information</h3>
                <div className="grid grid-cols-1 gap-2 text-xs">
                  <div className="flex justify-between">
                    <span className="font-medium text-gray-600">IPv4 Address:</span>
                    <span className="text-gray-800 font-mono">{userAgentData.ipv4Address || "Not detected"}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="font-medium text-gray-600">Browser:</span>
                    <span className="text-gray-800">{userAgentData.browserName}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="font-medium text-gray-600">Operating System:</span>
                    <span className="text-gray-800">{userAgentData.operatingSystem}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="font-medium text-gray-600">Submission Time:</span>
                    <span className="text-gray-800">{new Date(userAgentData.timestamp).toLocaleString()}</span>
                  </div>
                  <div className="mt-2 pt-2 border-t border-gray-300">
                    <div className="font-medium text-gray-600 mb-1">User Agent String:</div>
                    <div className="text-gray-800 break-all bg-white p-2 rounded border text-xs font-mono">
                      {userAgentData.userAgent || "Not available"}
                    </div>
                  </div>
                </div>
              </div>

              <button
                onClick={() => {
                  setSubmitted(false);
                  setFormData(prev => ({ ...prev, name: "", email: "", phone: "", domainName: "", subject: "", message: "", uploadedFiles: [], filePreviews: [], fileDescriptions: [] }));
                  setShowAttachment(false);
                  setCharacterCount(0);
                  setSubjectCharacterCount(0);
                  setUploadProgress(new Map());
                  setIsDragging(false);
                  clearAutoSavedData(); // Clear auto-saved data when starting new form
                }}
                className="mt-6 bg-gradient-to-r from-[#1a679f] to-blue-600 hover:from-blue-700 hover:to-blue-800 text-white px-8 py-3 rounded-lg font-semibold"
              >
                Submit Another Inquiry
              </button>
            </div>
          </div>
        </main>
      </div>
    );
  }

  // Drag and drop handlers
  const handleDragEnter = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(true);
  };

  const handleDragLeave = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);
  };

  const handleDragOver = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
  };

  const handleDrop = async (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setIsDragging(false);

    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
      await processFiles(files);
    }
  };

  const handleFileChange = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      const files = Array.from(e.target.files);
      await processFiles(files);
      e.target.value = '';
    }
  };

  // Centralized file processing with security validation
  const processFiles = async (files: File[]) => {
    const maxFileSize = 10 * 1024 * 1024; // 10MB
    const validFiles: File[] = [];
    const newPreviews: string[] = [];

    for (const file of files) {
      // Check file size
      if (file.size > maxFileSize) {
        showError(
          "File Too Large",
          `"${file.name}" exceeds the 10MB limit. Please choose a smaller file.`
        );
        continue;
      }

      // Basic file validation
      const fileName = file.name.toLowerCase();
      const fileType = file.type.toLowerCase();
      const fileExtension = fileName.substring(fileName.lastIndexOf('.'));

      // Allowed file types
      const allowedImageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'];
      const allowedDocExtensions = ['.pdf', '.txt', '.log'];
      const allowedArchiveExtensions = ['.zip', '.rar', '.7z'];

      let isValidFile = false;

      // Check if it's a valid file type
      if (allowedImageExtensions.includes(fileExtension) ||
          allowedDocExtensions.includes(fileExtension) ||
          allowedArchiveExtensions.includes(fileExtension)) {
        isValidFile = true;
      }

      if (!isValidFile) {
        showWarning(
          "File Type Not Allowed",
          `"${file.name}" is not a supported file type. Please upload images, PDFs, text files (.txt, .log), or safe archives (.zip, .rar, .7z).`
        );
        continue;
      }

      validFiles.push(file);

      // Create preview for images
      if (file.type.startsWith('image/')) {
        try {
          const preview = await new Promise<string>((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = (e) => {
              resolve(e.target?.result as string || '');
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
      setFormData(prev => ({
        ...prev,
        uploadedFiles: [...prev.uploadedFiles, ...validFiles],
        filePreviews: [...prev.filePreviews, ...newPreviews],
        fileDescriptions: [...prev.fileDescriptions, ...newDescriptions]
      }));
    }
  };

  // Main form rendering
  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">
      {/* Fixed Auto-save Indicator - Top Right */}
      <div className={`fixed top-4 right-4 z-50 flex items-center space-x-2 text-xs bg-white border border-gray-200 rounded-lg px-3 py-2 shadow-lg transition-all duration-300 ${
        autoSaveStatus === 'idle' ? 'opacity-0 translate-y-[-10px] pointer-events-none' : 'opacity-100 translate-y-0'
      }`}>
        {autoSaveStatus === 'saving' && (
          <>
            <svg className="w-3 h-3 animate-spin text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span className="text-blue-600 font-medium">Saving draft...</span>
          </>
        )}
        {autoSaveStatus === 'saved' && (
          <>
            <svg className="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
            </svg>
            <span className="text-green-600 font-medium">Draft saved automatically</span>
          </>
        )}
      </div>

      {/* Header */}
      <header className="bg-[#1a679f] text-white">
        <div className="container mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <img
                src="https://glowhost.com/wp-content/uploads/page_notag.png"
                alt="GlowHost"
                className="h-8 w-auto object-contain sm:h-10 md:h-12"
              />
            </div>
            <div className="text-right text-sm">
              <div className="text-cyan-200">24 / 7 / 365 Support</div>
              <div className="font-semibold">
                Toll Free Sales <a href="tel:+18882934678" className="hover:text-cyan-200">1 (888) 293-HOST</a>
              </div>
            </div>
          </div>
        </div>
      </header>

      {/* Breadcrumb */}
      <Breadcrumb items={getBreadcrumbConfig(formData.department).items} />

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        <div className="max-w-4xl mx-auto">
          <h2 className="text-2xl font-bold text-gray-800 mb-6">{getBreadcrumbConfig(formData.department).pageTitle}</h2>

          {/* Admin Testing Mode Banner - REMOVED for clean production UI */}

          {/* Form */}
          <div className="rounded-xl border bg-card text-card-foreground shadow-lg">
            <div className="p-6">
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Department - Only visible in testing mode */}
                {SHOW_DEPARTMENT_DROPDOWN && (
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">Department</label>
                    <select
                      name="department"
                      value={formData.department}
                      onChange={handleInputChange}
                      className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200 bg-white"
                      required
                    >
                      {getAvailableDepartments().map(department => (
                        <option key={department} value={department}>{department}</option>
                      ))}
                    </select>
                  </div>
                )}

                {/* Name and Email */}
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">
                      Full Name
                    </label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name}
                      onChange={handleInputChange}
                      placeholder="Enter your full name"
                      className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">
                      Email Address
                    </label>
                    <input
                      type="email"
                      name="email"
                      value={formData.email}
                      onChange={handleInputChange}
                      placeholder="Enter your email address"
                      className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                      required
                    />
                  </div>
                </div>

                {/* Phone and Domain */}
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">
                      Phone Number <span className="text-xs text-gray-500">(Optional)</span>
                    </label>
                    <input
                      type="tel"
                      name="phone"
                      value={formData.phone}
                      onChange={handleInputChange}
                      placeholder="Enter your phone number"
                      className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                    />
                  </div>
                  <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">
                      Domain Name <span className="text-xs text-gray-500">(Optional)</span>
                    </label>
                    <input
                      type="text"
                      name="domainName"
                      value={formData.domainName}
                      onChange={handleInputChange}
                      placeholder="example.com"
                      className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                    />
                  </div>
                </div>

                {/* Subject */}
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <label className="text-sm font-medium text-gray-700">
                      Subject
                    </label>
                    <span className="text-xs text-gray-500">{subjectCharacterCount}/250 characters</span>
                  </div>
                  <input
                    type="text"
                    name="subject"
                    value={formData.subject}
                    onChange={handleInputChange}
                    placeholder="Brief description of your inquiry"
                    className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                    maxLength={250}
                    required
                  />
                </div>

                {/* Message */}
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <label className="text-sm font-medium text-gray-700">
                      Message
                    </label>
                    <span className="text-xs text-gray-500">{characterCount}/10000 characters</span>
                  </div>
                  <textarea
                    name="message"
                    value={formData.message}
                    onChange={handleInputChange}
                    placeholder="Please provide details about your hosting needs, questions, or requirements..."
                    className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                    rows={6}
                    maxLength={10000}
                    required
                  />
                </div>

                {/* Attachments Section */}
                <div className="space-y-4">
                  <div className="flex items-center">
                    <h3 className="text-lg font-semibold text-gray-800">Attachments</h3>
                    <span className="text-xs text-gray-500 ml-1">(Optional)</span>
                  </div>

                  {!showAttachment ? (
                    <button
                      type="button"
                      onClick={() => setShowAttachment(true)}
                      className="w-full p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group"
                    >
                      <div className="flex items-center justify-center space-x-2 text-gray-600 group-hover:text-blue-600">
                        <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        <span className="font-medium">Add Files</span>
                      </div>
                    </button>
                  ) : (
                    <div className="space-y-4">
                      {/* Drag and Drop Zone */}
                      <div
                        onDragEnter={handleDragEnter}
                        onDragLeave={handleDragLeave}
                        onDragOver={handleDragOver}
                        onDrop={handleDrop}
                        className={`relative border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200 ${
                          isDragging
                            ? 'border-blue-500 bg-blue-50'
                            : 'border-gray-300 hover:border-gray-400 bg-gray-50'
                        }`}
                      >
                        <input
                          type="file"
                          onChange={handleFileChange}
                          multiple
                          accept="image/*,.pdf,.txt,.zip,.rar,.7z,.log"
                          className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                          id="file-upload"
                        />

                        <div className="space-y-3">
                          <div className={`mx-auto w-12 h-12 rounded-full flex items-center justify-center transition-colors ${
                            isDragging ? 'bg-blue-200' : 'bg-gray-200'
                          }`}>
                            <svg className={`w-6 h-6 ${isDragging ? 'text-blue-600' : 'text-gray-600'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                          </div>
                          <div>
                            <p className="text-sm font-medium text-gray-700">
                              {isDragging ? 'Drop files here' : 'Drag & drop files here, or click to browse'}
                            </p>
                            <p className="text-xs text-gray-500 mt-1">
                              Supports: Images, PDFs, Text files, Archives, Log files (Max 10MB each)
                            </p>
                          </div>
                        </div>
                      </div>

                      {/* Uploaded Files Display */}
                      {formData.uploadedFiles.length > 0 && (
                        <div className="space-y-3">
                          <div className="flex items-center justify-between">
                            <h4 className="text-sm font-semibold text-gray-700">
                              {formData.uploadedFiles.length} file{formData.uploadedFiles.length !== 1 ? 's' : ''} uploaded
                            </h4>
                            <button
                              type="button"
                              onClick={() => {
                                setFormData(prev => ({
                                  ...prev,
                                  uploadedFiles: [],
                                  filePreviews: [],
                                  fileDescriptions: []
                                }));
                                setUploadProgress(new Map());
                              }}
                              className="text-xs text-red-600 hover:text-red-800 font-medium"
                            >
                              Remove all
                            </button>
                          </div>

                          <div className="grid gap-3">
                            {formData.uploadedFiles.map((file, index) => (
                              <div
                                key={index}
                                className="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow"
                              >
                                <div className="flex items-start space-x-4">
                                  {/* File Preview/Icon */}
                                  <div className="flex-shrink-0">
                                    {formData.filePreviews[index] ? (
                                      <div className="w-16 h-16 rounded-lg overflow-hidden bg-gray-100">
                                        <img
                                          src={formData.filePreviews[index]}
                                          alt={file.name}
                                          className="w-full h-full object-cover"
                                        />
                                      </div>
                                    ) : (
                                      <div className="w-16 h-16 rounded-lg flex items-center justify-center bg-gray-100 text-gray-600">
                                        <span className="text-2xl">
                                          {file.type.startsWith('image/') ? '🖼️' :
                                           file.type.includes('pdf') ? '📄' :
                                           file.type.includes('zip') || file.type.includes('rar') ? '🗜️' : '📎'}
                                        </span>
                                      </div>
                                    )}
                                  </div>

                                  {/* File Info and Description */}
                                  <div className="flex-1 min-w-0">
                                    <div className="flex items-start justify-between">
                                      <div>
                                        <p className="text-sm font-medium text-gray-900 truncate pr-2" title={file.name}>
                                          {file.name}
                                        </p>
                                        <p className="text-xs text-gray-500 mt-1">
                                          {formatFileSize(file.size)} • {file.type || 'Unknown type'}
                                        </p>
                                      </div>
                                      <button
                                        type="button"
                                        onClick={() => {
                                          setFormData(prev => ({
                                            ...prev,
                                            uploadedFiles: prev.uploadedFiles.filter((_, i) => i !== index),
                                            filePreviews: prev.filePreviews.filter((_, i) => i !== index),
                                            fileDescriptions: prev.fileDescriptions.filter((_, i) => i !== index)
                                          }));
                                        }}
                                        className="flex items-center justify-center w-8 h-8 bg-red-50 hover:bg-red-100 border border-red-200 hover:border-red-300 rounded-full text-red-500 hover:text-red-700 transition-all duration-200 hover:scale-110 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                        title="Remove file"
                                        aria-label={`Remove ${file.name}`}
                                      >
                                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth={2.5}>
                                          <path strokeLinecap="round" strokeLinejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                      </button>
                                    </div>

                                    {/* Description Field */}
                                    <div className="mt-3">
                                      <label className="text-sm font-medium text-gray-700 block mb-1">
                                        Description <span className="text-xs text-gray-500">(Optional)</span>
                                      </label>
                                      <input
                                        type="text"
                                        value={formData.fileDescriptions[index] || ''}
                                        onChange={(e) => {
                                          const alphanumericValue = e.target.value.replace(/[^a-zA-Z0-9\s]/g, '');
                                          setFormData(prev => {
                                            const newDescriptions = [...prev.fileDescriptions];
                                            newDescriptions[index] = alphanumericValue;
                                            return {
                                              ...prev,
                                              fileDescriptions: newDescriptions
                                            };
                                          });
                                        }}
                                        placeholder="Add a description for this file"
                                        className="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:ring-2 focus:ring-[#1a679f]/20 hover:border-gray-400 transition-all duration-200"
                                        maxLength={150}
                                      />
                                      <p className="text-xs text-gray-500 mt-1">
                                        {formData.fileDescriptions[index]?.length || 0}/150 characters • Letters and numbers only
                                      </p>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>
                      )}
                    </div>
                  )}
                </div>

                {/* Submit Button */}
                <div className="flex justify-center pt-6">
                  <div className="flex flex-col sm:flex-row gap-4 items-center">
                    <button
                      type="submit"
                      disabled={!isFormValid() || isSubmitting}
                      className="w-full sm:w-auto bg-gradient-to-r from-[#1a679f] to-blue-600 hover:from-blue-700 hover:to-blue-800 text-white px-12 py-6 text-xl font-bold rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                      {isSubmitting ? "Submitting..." : "Submit Request"}
                    </button>

                    {/* Quick Test Button - Only in Local Testing Mode */}
                    {!USE_PHP_BACKEND && (
                      <button
                        type="button"
                        onClick={() => {
                          setReferenceId(`TEST-${Math.floor(Math.random() * 1000000)}`);
                          setSubmitted(true);
                          clearAutoSavedData();
                          showSuccess("Quick Test", "Skipped to confirmation page for testing!");
                        }}
                        className="px-6 py-3 bg-purple-100 hover:bg-purple-200 text-purple-700 font-semibold rounded-lg border border-purple-300 hover:border-purple-400 transition-all duration-200 text-sm"
                      >
                        🚀 Skip to Confirmation
                      </button>
                    )}
                  </div>
                </div>

                <div className="flex justify-center pt-2">
                  <p className="text-sm text-gray-500">
                    {isFormValid() ? "Ready to submit" : "Please complete all required fields to submit"}
                    {!USE_PHP_BACKEND && (
                      <span className="ml-2 text-purple-600">• Quick test mode enabled</span>
                    )}
                  </p>
                </div>
              </form>
            </div>
          </div>
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-gray-100 border-t">
        <div className="container mx-auto px-4 py-6">
          <div className="text-center space-y-2">
            <p className="text-sm text-gray-600">Sales Contact Form Version: 273</p>

            {/* Testing Mode Indicator */}
            <div className={`inline-flex items-center px-3 py-1 rounded-full text-xs font-medium ${
              USE_PHP_BACKEND
                ? 'bg-green-100 text-green-800 border border-green-200'
                : 'bg-blue-100 text-blue-800 border border-blue-200'
            }`}>
              <div className={`w-2 h-2 rounded-full mr-2 ${USE_PHP_BACKEND ? 'bg-green-500' : 'bg-blue-500'}`} />
              {USE_PHP_BACKEND ? '🔧 PHP Backend Mode' : '🧪 Local Testing Mode'}
            </div>

            <div className="text-xs text-gray-500 max-w-md mx-auto">
              {USE_PHP_BACKEND ? (
                <p>Form submissions will be sent to <code className="bg-gray-200 px-1 rounded">/api/submit-form.php</code></p>
              ) : (
                <div className="space-y-1">
                  <p>Form submissions are simulated (not sent to PHP backend)</p>
                  {ENABLE_TEST_DATA && <p className="text-purple-600 font-medium">⚡ Test data pre-filled for rapid testing</p>}
                  {ENABLE_DEBUG_MODE && <p className="text-blue-600 font-medium">🔍 Debug mode active - check console</p>}
                  <p>
                    <span className="text-gray-400">Mode controlled by: </span>
                    <a href="/admin/" className="text-blue-600 hover:underline">Admin Settings</a>
                    <span className="text-gray-400"> → Development & Testing</span>
                  </p>
                </div>
              )}
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
