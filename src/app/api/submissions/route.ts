import { NextResponse } from 'next/server';

export async function GET() {
  return NextResponse.json({
    submissions: [
      {
        id: 'GH-A7F3-250108',
        name: 'Sarah Johnson',
        email: 'sarah.j@techcorp.com',
        subject: 'Enterprise hosting inquiry',
        department: 'Sales Questions',
        status: 'new'
      }
    ],
    total: 47
  });
}
