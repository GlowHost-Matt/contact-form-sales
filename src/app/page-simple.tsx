"use client";

import { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { useToast } from "@/hooks/use-toast";

interface ContactFormData {
  name: string;
  email: string;
  phone: string;
  domain: string;
  department: string;
  subject: string;
  message: string;
}

const DEPARTMENTS = [
  "Sales Questions",
  "Technical Support",
  "Billing Support",
  "General Inquiry"
];

export default function ContactPage() {
  const { toast } = useToast();
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitted, setSubmitted] = useState(false);
  const [referenceId, setReferenceId] = useState("");

  const [formData, setFormData] = useState<ContactFormData>({
    name: "",
    email: "",
    phone: "",
    domain: "",
    department: DEPARTMENTS[0],
    subject: "",
    message: ""
  });

  const handleInputChange = (field: keyof ContactFormData, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const isFormValid = () => {
    return formData.name.trim() &&
           formData.email.trim() &&
           formData.subject.trim() &&
           formData.message.trim() &&
           formData.department;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (!isFormValid()) {
      toast({
        title: "Validation Error",
        description: "Please fill in all required fields",
        variant: "destructive"
      });
      return;
    }

    setIsSubmitting(true);

    try {
      const response = await fetch('/api/contact', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
      });

      const result = await response.json();

      if (result.success) {
        setReferenceId(result.referenceId);
        setSubmitted(true);
        toast({
          title: "Success!",
          description: "Your inquiry has been submitted successfully"
        });
      } else {
        throw new Error(result.message || 'Submission failed');
      }
    } catch (error) {
      console.error('Submission error:', error);
      toast({
        title: "Submission Failed",
        description: error instanceof Error ? error.message : "Please try again",
        variant: "destructive"
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  if (submitted) {
    return (
      <div className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">

        {/* Header */}
        <header className="bg-[#1a679f] text-white">
          <div className="container mx-auto px-4 py-6">
            <div className="flex items-center justify-between">
              <div className="flex items-center">
                <img
                  src="https://glowhost.com/wp-content/uploads/page_notag.png"
                  alt="GlowHost"
                  className="h-10 w-auto object-contain"
                />
              </div>
              <div className="text-right text-sm">
                <div className="text-cyan-200">24/7/365 Support</div>
                <div className="font-semibold">
                  Toll Free Sales{" "}
                  <a href="tel:+18882934678" className="hover:text-cyan-200">
                    1 (888) 293-HOST
                  </a>
                </div>
              </div>
            </div>
          </div>
        </header>

        {/* Success Message */}
        <main className="container mx-auto px-4 py-12">
          <div className="max-w-2xl mx-auto">
            <Card className="text-center">
              <CardHeader>
                <div className="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                  <svg className="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <CardTitle className="text-2xl text-green-800">Thank You!</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <p className="text-gray-700">
                  Your inquiry has been submitted successfully. We'll respond within 24 hours.
                </p>
                <div className="bg-gray-50 p-4 rounded-lg">
                  <p className="text-sm text-gray-600 mb-2">Reference ID</p>
                  <p className="text-xl font-mono font-bold">{referenceId}</p>
                </div>
                <Button
                  onClick={() => {
                    setSubmitted(false);
                    setFormData({
                      name: "",
                      email: "",
                      phone: "",
                      domain: "",
                      department: DEPARTMENTS[0],
                      subject: "",
                      message: ""
                    });
                  }}
                  className="bg-[#1a679f] hover:bg-[#155a85]"
                >
                  Submit Another Inquiry
                </Button>
              </CardContent>
            </Card>
          </div>
        </main>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100">

      {/* Header */}
      <header className="bg-[#1a679f] text-white">
        <div className="container mx-auto px-4 py-6">
          <div className="flex items-center justify-between">
            <div className="flex items-center">
              <img
                src="https://glowhost.com/wp-content/uploads/page_notag.png"
                alt="GlowHost"
                className="h-10 w-auto object-contain"
              />
            </div>
            <div className="text-right text-sm">
              <div className="text-cyan-200">24/7/365 Support</div>
              <div className="font-semibold">
                Toll Free Sales{" "}
                <a href="tel:+18882934678" className="hover:text-cyan-200">
                  1 (888) 293-HOST
                </a>
              </div>
            </div>
          </div>
        </div>
      </header>

      {/* Breadcrumb */}
      <nav className="bg-white border-b">
        <div className="container mx-auto px-4 py-3">
          <div className="flex items-center space-x-2 text-sm">
            <a href="#" className="text-[#1a679f] hover:underline">Web Hosting Support</a>
            <span className="text-gray-500">»</span>
            <span className="text-gray-700">Contact GlowHost Sales</span>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="container mx-auto px-4 py-8">
        <div className="max-w-4xl mx-auto">
          <h1 className="text-3xl font-bold text-gray-900 mb-8">
            Contact GlowHost Sales: New Inquiry
          </h1>

          <Card>
            <CardHeader>
              <CardTitle>Send us your inquiry</CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-6">
                {/* Department Selection */}
                <div className="space-y-2">
                  <Label htmlFor="department">Department</Label>
                  <Select
                    value={formData.department}
                    onValueChange={(value) => handleInputChange('department', value)}
                  >
                    <SelectTrigger>
                      <SelectValue placeholder="Select a department" />
                    </SelectTrigger>
                    <SelectContent>
                      {DEPARTMENTS.map(dept => (
                        <SelectItem key={dept} value={dept}>{dept}</SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                </div>

                {/* Name and Email */}
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="name">Full Name *</Label>
                    <Input
                      id="name"
                      type="text"
                      placeholder="Enter your full name"
                      value={formData.name}
                      onChange={(e) => handleInputChange('name', e.target.value)}
                      required
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="email">Email Address *</Label>
                    <Input
                      id="email"
                      type="email"
                      placeholder="Enter your email address"
                      value={formData.email}
                      onChange={(e) => handleInputChange('email', e.target.value)}
                      required
                    />
                  </div>
                </div>

                {/* Phone and Domain */}
                <div className="grid md:grid-cols-2 gap-6">
                  <div className="space-y-2">
                    <Label htmlFor="phone">
                      Phone Number <span className="text-gray-500">(Optional)</span>
                    </Label>
                    <Input
                      id="phone"
                      type="tel"
                      placeholder="Enter your phone number"
                      value={formData.phone}
                      onChange={(e) => handleInputChange('phone', e.target.value)}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="domain">
                      Domain Name <span className="text-gray-500">(Optional)</span>
                    </Label>
                    <Input
                      id="domain"
                      type="text"
                      placeholder="example.com"
                      value={formData.domain}
                      onChange={(e) => handleInputChange('domain', e.target.value)}
                    />
                  </div>
                </div>

                {/* Subject */}
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <Label htmlFor="subject">Subject *</Label>
                    <span className="text-xs text-gray-500">
                      {formData.subject.length}/250 characters
                    </span>
                  </div>
                  <Input
                    id="subject"
                    type="text"
                    placeholder="Brief description of your inquiry"
                    value={formData.subject}
                    onChange={(e) => handleInputChange('subject', e.target.value)}
                    maxLength={250}
                    required
                  />
                </div>

                {/* Message */}
                <div className="space-y-2">
                  <div className="flex justify-between items-center">
                    <Label htmlFor="message">Message *</Label>
                    <span className="text-xs text-gray-500">
                      {formData.message.length}/5000 characters
                    </span>
                  </div>
                  <Textarea
                    id="message"
                    placeholder="Please provide details about your hosting needs, questions, or requirements..."
                    value={formData.message}
                    onChange={(e) => handleInputChange('message', e.target.value)}
                    maxLength={5000}
                    rows={6}
                    required
                  />
                </div>

                {/* Submit Button */}
                <div className="flex justify-center pt-6">
                  <Button
                    type="submit"
                    disabled={!isFormValid() || isSubmitting}
                    className="px-12 py-6 text-lg bg-gradient-to-r from-[#1a679f] to-blue-600 hover:from-blue-700 hover:to-blue-800"
                  >
                    {isSubmitting ? "Submitting..." : "Submit Request"}
                  </Button>
                </div>

                <div className="text-center text-sm text-gray-500">
                  {isFormValid() ? "Ready to submit" : "Please complete all required fields"}
                </div>
              </form>
            </CardContent>
          </Card>
        </div>
      </main>

      {/* Footer */}
      <footer className="bg-gray-100 border-t mt-12">
        <div className="container mx-auto px-4 py-6">
          <div className="text-center text-sm text-gray-600">
            <p>GlowHost Contact Form • Professional hosting solutions since 2002</p>
          </div>
        </div>
      </footer>
    </div>
  );
}
