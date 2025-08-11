import { NextResponse } from 'next/server';

export async function GET() {
  return NextResponse.json({
    stats: {
      total_submissions: 47,
      new_submissions: 8,
      files_uploaded: 23,
      avg_response_time: '2.4h'
    },
    recent_submissions: [
      {
        id: 'GH-A7F3-250108',
        name: 'Sarah Johnson',
        email: 'sarah.j@techcorp.com',
        department: 'Sales Questions',
        status: 'new',
        timestamp: new Date().toISOString()
      }
    ]
  });
}
