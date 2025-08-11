import { NextRequest, NextResponse } from 'next/server';

export async function POST(request: NextRequest) {
  try {
    const data = await request.json();

    // Validate form data
    const errors = [];
    if (!data.name || data.name.trim().length < 2) {
      errors.push('Name is required and must be at least 2 characters');
    }
    if (!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
      errors.push('Valid email address is required');
    }
    if (!data.subject || data.subject.trim().length < 5) {
      errors.push('Subject is required and must be at least 5 characters');
    }
    if (!data.message || data.message.trim().length < 10) {
      errors.push('Message is required and must be at least 10 characters');
    }

    if (errors.length > 0) {
      return NextResponse.json(
        { success: false, error: 'Validation failed', errors },
        { status: 400 }
      );
    }

    // Generate reference ID
    const referenceId = `GH-${Math.random().toString(36).substr(2, 9).toUpperCase()}`;

    // In production, this would save to database
    console.log('Form submitted:', {
      id: referenceId,
      department: data.department,
      name: data.name,
      email: data.email,
      subject: data.subject
    });

    return NextResponse.json({
      success: true,
      reference_id: referenceId,
      message: 'Form submitted successfully'
    });
  } catch (error) {
    console.error('Form submission error:', error);
    return NextResponse.json(
      { success: false, error: 'Internal server error' },
      { status: 500 }
    );
  }
}
