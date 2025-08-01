import React from 'react';
import { usePageFocus } from '@/hooks/usePageFocus';

interface ConfirmationPageProps {
  referenceId: string;
  customerName: string;
  email: string;
  subject: string;
  message: string;
  department: string;
  domainName: string;
  submissionTimestamp: string;
  ipv4Address: string;
  browserName: string;
  operatingSystem: string;
  userAgent: string;
}

export const ConfirmationPage: React.FC<ConfirmationPageProps> = ({
  referenceId,
  customerName,
  email,
  subject,
  message,
  department,
  domainName,
  submissionTimestamp,
  ipv4Address,
  browserName,
  operatingSystem,
  userAgent
}) => {
  const headingRef = usePageFocus<HTMLHeadingElement>();

  const handleReviewTicket = () => {
    const params = new URLSearchParams({
      ref: referenceId,
      name: customerName,
      email: email,
      subject: subject,
      message: encodeURIComponent(message),
      dept: department,
      domain: domainName,
      time: submissionTimestamp,
      ipv4: ipv4Address,
      browser: browserName,
      os: operatingSystem,
      ua: userAgent
    });
    window.location.href = `/support-thread?${params.toString()}`;
  };

  return (
    <div className="max-w-4xl mx-auto">
      <div className="bg-white border border-gray-200 rounded-xl p-8 text-center shadow-sm">
        <h1 ref={headingRef} className="text-2xl font-bold text-gray-800 mb-2" tabIndex={-1}>
          Thank You!
        </h1>
        <p className="text-gray-700 mb-2">Your sales inquiry has been submitted successfully.</p>
        <p className="text-gray-600 mb-6">A copy of your submission has been sent to your email address.</p>

        <div className="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden text-left">
          <div className="p-6">
            <div className="space-y-4">
              {/* Ticket Status */}
              <div className="flex items-center justify-between py-3 border-b border-gray-100">
                <div className="flex items-center">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Ticket Status</label>
                </div>
                <div className="text-right">
                  <div className="flex items-center justify-end space-x-2">
                    <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                    <p className="text-sm sm:text-base text-gray-900 font-medium">Open</p>
                  </div>
                  <p className="text-xs sm:text-sm text-gray-500 mt-1">Under review by our team</p>
                </div>
              </div>

              {/* Subject */}
              <div className="py-3 border-b border-gray-100">
                <div className="flex items-center mb-2">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1.586l-4 4z" /></svg>
                  <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Subject</label>
                </div>
                <div className="text-right">
                  <p className="text-sm sm:text-base font-semibold text-gray-900 break-words" title={subject}>
                    {subject}
                  </p>
                </div>
              </div>

              {/* Department */}
              <div className="flex items-center justify-between py-3 border-b border-gray-100">
                <div className="flex items-center">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                  <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Department</label>
                </div>
                <div className="text-right">
                  <p className="text-sm sm:text-base text-gray-900 font-medium">
                    {department}
                  </p>
                </div>
              </div>

              {/* Reference ID */}
              <div className="flex items-center justify-between py-3 border-b border-gray-100">
                <div className="flex items-center">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
                  <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Reference ID</label>
                </div>
                <div className="text-right">
                  <p className="text-sm sm:text-base font-mono font-bold text-gray-900 bg-gray-50 px-2 sm:px-3 py-1 rounded-md border">
                    {referenceId}
                  </p>
                </div>
              </div>

              {/* Creation Date */}
              <div className="flex items-center justify-between py-3">
                <div className="flex items-center">
                  <svg className="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <label className="text-sm font-medium text-gray-500 uppercase tracking-wide">Creation Date</label>
                </div>
                <div className="text-right">
                  <p className="text-sm sm:text-base text-gray-900 font-medium">
                    {new Date(submissionTimestamp).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                  </p>
                  <p className="text-xs sm:text-sm text-gray-500">
                    {new Date(submissionTimestamp).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', timeZoneName: 'short' })}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div className="flex justify-center items-center gap-4 mt-8">
          <button
            onClick={handleReviewTicket}
            className="w-full sm:w-auto bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-8 py-3 rounded-lg border border-gray-300 hover:border-gray-400 transition-all duration-200"
          >
            Review Your Ticket
          </button>
        </div>

        {/* Technical Info Box */}
        <div className="bg-gray-50 border border-gray-200 rounded-lg p-4 mt-4 text-left">
          <h3 className="text-sm font-semibold text-gray-700 mb-3">Technical Information</h3>
          <div className="grid grid-cols-1 gap-2 text-xs">
            <div className="flex justify-between">
              <span className="font-medium text-gray-600">IPv4 Address:</span>
              <span className="text-gray-800 font-mono">{ipv4Address || "Not detected"}</span>
            </div>
            <div className="flex justify-between">
              <span className="font-medium text-gray-600">Browser:</span>
              <span className="text-gray-800">{browserName}</span>
            </div>
            <div className="flex justify-between">
              <span className="font-medium text-gray-600">Operating System:</span>
              <span className="text-gray-800">{operatingSystem}</span>
            </div>
            <div className="flex justify-between">
              <span className="font-medium text-gray-600">Submission Time:</span>
              <span className="text-gray-800">{new Date(submissionTimestamp).toLocaleString()}</span>
            </div>
            <div className="mt-2 pt-2 border-t border-gray-300">
              <div className="font-medium text-gray-600 mb-1">User Agent String:</div>
              <div className="text-gray-800 break-all bg-white p-2 rounded border text-xs font-mono">
                {userAgent}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
