import React from 'react';
import { useFormField } from '@/hooks/useFormField';
import { useAutoSave } from '@/hooks/useAutoSave';
import { Attachments } from '@/components/ui/Attachments';
import { useFileHandling } from '@/hooks/useFileHandling';
import { AutoSaveIndicator } from '@/components/ui/AutoSaveIndicator';
import { FORM_AUTO_SAVE_CONFIGS } from '../../../config/features.config';
import { useNotificationHelpers } from '@/components/ui/notification';

interface TicketThreadProps {
  referenceId: string;
  customerName: string;
  email: string;
  subject: string;
  message: string;
  department: string;
  domainName: string;
  submissionTimestamp: string;
}

export const TicketThread: React.FC<TicketThreadProps> = ({
  referenceId,
  customerName,
  email,
  subject,
  message,
  department,
  domainName,
  submissionTimestamp
 }) => {
  const replyField = useFormField();
  const [showAttachment, setShowAttachment] = React.useState(false);
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
    removeFile,
    removeAllFiles
  } = useFileHandling();

  const { showSuccess } = useNotificationHelpers();

  // Enhanced Auto-Save for Thread Replies
  const autoSaveData = {
    reply: replyField.value,
    showAttachment,
    fileDescriptions
  };

  const {
    status: autoSaveStatus,
    clear: clearAutoSavedData,
  } = useAutoSave(autoSaveData, {
    formType: 'support-thread',
    userId: referenceId, // Use ticket ID as user identifier
    fields: ['reply', 'showAttachment', 'fileDescriptions'],
    onError: (error) => {
      console.warn('Auto-save failed for thread reply:', error);
    }
  });

  return (
    <div className="max-w-4xl mx-auto">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-2xl font-bold text-gray-800">Ticket Thread: {referenceId}</h2>
          <AutoSaveIndicator formType={'support-thread'} size="sm" />
        </div>


        <div className="bg-white border border-gray-200 rounded-xl p-6 shadow-sm text-left">
            <h3 className="text-lg font-semibold text-gray-800 mb-4 flex items-center">
              <svg className="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-3.582 8-8 8a8.955 8.955 0 01-4.126-.98L3 20l1.98-5.874A8.955 8.955 0 013 12c0-4.418 3.582-8 8-8s8 3.582 8 8z" />
              </svg>
              Support Thread
            </h3>

            {/* Customer Original Post */}
            <div className="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
              {/* Post Header */}
              <div className="bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    {/* User Avatar */}
                    <div className="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-semibold text-sm leading-none shrink-0">
                      <span className="block text-center">
                        {customerName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)}
                      </span>
                    </div>

                    {/* User Info */}
                    <div>
                      <div className="font-semibold text-gray-900">{customerName}</div>
                      <div className="text-sm text-gray-600">
                        {email}
                      </div>
                    </div>
                  </div>
                </div>

                {/* Subject Line */}
                <div className="mt-3 pt-3 border-t border-green-200">
                  <div className="flex items-center space-x-2">
                    <span className="text-xs font-medium text-green-700 bg-green-200 px-2 py-1 rounded-full">
                      {department}
                    </span>
                    {domainName && (
                      <span className="text-xs font-medium text-gray-600 bg-gray-200 px-2 py-1 rounded-full">
                        {domainName}
                      </span>
                    )}
                  </div>
                  <h4 className="text-base font-semibold text-gray-900 mt-2">
                    {subject}
                  </h4>
                </div>

                {/* Updated At */}
                <div className="mt-3 pt-2 border-t border-green-100">
                  <div className="text-xs text-gray-500 text-right">
                    Updated at: {new Date(submissionTimestamp).toLocaleString('en-US', {
                      month: 'short',
                      day: 'numeric',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                      second: '2-digit'
                    })} EST
                  </div>
                </div>
              </div>

              {/* Post Body */}
              <div className="p-4">
                <div className="prose prose-gray max-w-none">
                  <div className="text-gray-800 whitespace-pre-wrap leading-relaxed">
                    {message}
                  </div>
                </div>
              </div>
            </div>

            {/* Staff Response */}
            <div className="mt-4 bg-white rounded-lg border border-gray-200 overflow-hidden">
              {/* Staff Post Header */}
              <div className="bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-3 border-b border-gray-200">
                <div className="flex items-center justify-between">
                  <div className="flex items-center space-x-3">
                    {/* Staff Avatar */}
                    <div className="w-10 h-10 bg-[#1a679f] rounded-full flex items-center justify-center text-white font-semibold text-sm leading-none shrink-0">
                      <span className="block text-center">
                        GH
                      </span>
                    </div>

                    {/* Staff Info */}
                    <div>
                      <div className="font-semibold text-gray-900">Sarah Mitchell</div>
                      <div className="text-sm text-gray-600">
                        GlowHost Sales Specialist
                      </div>
                    </div>
                  </div>
                </div>

                {/* Updated At */}
                <div className="mt-3 pt-2 border-t border-blue-100">
                  <div className="text-xs text-gray-500 text-right">
                    Updated at: {new Date(Date.now() + 2 * 60 * 60 * 1000).toLocaleString('en-US', {
                      month: 'short',
                      day: 'numeric',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                      second: '2-digit'
                    })} EST
                  </div>
                </div>
              </div>

              {/* Staff Post Body */}
              <div className="p-4">
                <div className="prose prose-gray max-w-none">
                  <div className="text-gray-800 whitespace-pre-wrap leading-relaxed">
                    Hi {customerName.split(' ')[0]},

Thank you for the quick response! The Business Pro Plan sounds exactly like what I need.

I'm particularly interested in the PCI compliance assistance since we'll be processing payments directly on the site.

Looking forward to your detailed quote!

Best,
Sarah
                  </div>
                </div>
              </div>
            </div>

            {/* Reply Text Area */}
            <div className="mt-6">
              <div className="bg-white rounded-lg border border-gray-200 p-4">
                <div className="flex justify-between items-center mb-2">
                  <label htmlFor="reply-message" className="block text-sm font-medium text-gray-700">
                    Add your reply
                  </label>
                  <div className={`text-xs text-gray-500`}>
                    {replyField.characterCount}/10000 characters
                  </div>
                </div>
                <textarea
                  id="reply-message"
                  name="reply-message"
                  rows={4}
                  className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                  placeholder="Type your reply here..."
                  maxLength={10000}
                  value={replyField.value}
                  onChange={replyField.onChange}
                />
                <div className="flex justify-between items-center mt-3">
                  {!showAttachment && (
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
                  )}
                </div>
              </div>
              {showAttachment && (
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

            {/* Reply Button */}
            <div className="mt-4 text-center">
              <button
                className="inline-flex items-center px-6 py-3 bg-gradient-to-r from-[#1a679f] to-blue-600 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed"
              >
                  <>
                    <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    Reply to Thread
                  </>
              </button>
            </div>
        </div>
    </div>
  );
};
