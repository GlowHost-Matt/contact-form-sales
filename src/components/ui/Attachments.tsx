import React from 'react';

interface AttachmentsProps {
  uploadedFiles: File[];
  filePreviews: string[];
  fileDescriptions: string[];
  onFileChange: (e: React.ChangeEvent<HTMLInputElement>) => void;
  onDrop: (e: React.DragEvent<HTMLDivElement>) => void;
  onDragEnter: (e: React.DragEvent<HTMLDivElement>) => void;
  onDragLeave: (e: React.DragEvent<HTMLDivElement>) => void;
  onDragOver: (e: React.DragEvent<HTMLDivElement>) => void;
  isDragging: boolean;
  setFileDescriptions: React.Dispatch<React.SetStateAction<string[]>>;
  removeFile: (index: number) => void;
  removeAllFiles: () => void;
}

export const Attachments: React.FC<AttachmentsProps> = ({
  uploadedFiles,
  filePreviews,
  fileDescriptions,
  onFileChange,
  onDrop,
  onDragEnter,
  onDragLeave,
  onDragOver,
  isDragging,
  setFileDescriptions,
  removeFile,
  removeAllFiles
}) => {
    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

  return (
    <div className="space-y-4">
      {/* Drag and Drop Zone */}
      <div
        onDragEnter={onDragEnter}
        onDragLeave={onDragLeave}
        onDragOver={onDragOver}
        onDrop={onDrop}
        className={`relative border-2 border-dashed rounded-lg p-8 text-center transition-all duration-200 ${
          isDragging
            ? 'border-blue-500 bg-blue-50'
            : 'border-gray-300 hover:border-gray-400 bg-gray-50'
        }`}
      >
        <input
          type="file"
          onChange={onFileChange}
          multiple
          accept="image/*,.pdf,.txt,.zip,.rar,.7z,.log"
          className="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
          id="file-upload"
        />

        <div className="space-y-3">
          <div className={`mx-auto w-12 h-12 rounded-lg flex items-center justify-center transition-colors ${
            isDragging ? 'bg-blue-200' : 'bg-gray-200'
          }`}>
            <svg className={`w-6 h-6 ${isDragging ? 'text-blue-600' : 'text-gray-600'}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
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
      {uploadedFiles.length > 0 && (
        <div className="space-y-3">
          <div className="flex items-center justify-between">
            <h4 className="text-sm font-semibold text-gray-700">
              {uploadedFiles.length} file{uploadedFiles.length !== 1 ? 's' : ''} uploaded
            </h4>
            <button
              type="button"
              onClick={removeAllFiles}
              className="text-xs text-red-600 hover:text-red-800 font-medium"
            >
              Remove all
            </button>
          </div>

          <div className="grid gap-3">
            {uploadedFiles.map((file, index) => (
              <div
                key={index}
                className="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow"
              >
                <div className="flex items-start space-x-4">
                  {/* File Preview/Icon */}
                  <div className="flex-shrink-0">
                    {filePreviews[index] ? (
                      <div className="w-16 h-16 rounded-lg overflow-hidden bg-gray-100">
                        <img
                          src={filePreviews[index]}
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
                        onClick={() => removeFile(index)}
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
                        Description <span className="text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-md ml-2">Optional</span>
                      </label>
                      <input
                        type="text"
                        value={fileDescriptions[index] || ''}
                        onChange={(e) => {
                          const alphanumericValue = e.target.value.replace(/[^a-zA-Z0-9\s]/g, '');
                            const newDescriptions = [...fileDescriptions];
                            newDescriptions[index] = alphanumericValue;
                            setFileDescriptions(newDescriptions);
                        }}
                        placeholder="Add a description for this file"
                        className="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-[#1a679f] focus:border-2 hover:border-gray-400 hover:shadow-sm transition-all duration-200"
                        maxLength={150}
                      />
                      <p className="text-xs text-gray-500 mt-1">
                        {fileDescriptions[index]?.length || 0}/150 characters • Letters and numbers only
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
  );
};
