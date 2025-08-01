"use client";

import { Suspense } from 'react';
import { useSearchParams } from 'next/navigation';
import { MainLayout } from '@/components/layout/MainLayout';
import { TicketThread } from '@/components/ui/TicketThread';
import Link from 'next/link';

const SupportThreadPage = () => {
  const searchParams = useSearchParams();

  const referenceId = searchParams.get('ref') || 'N/A';
  const customerName = searchParams.get('name') || 'N/A';
  const email = searchParams.get('email') || 'N/A';
  const subject = searchParams.get('subject') || 'No subject provided';
  const message = decodeURIComponent(searchParams.get('message') || 'No message provided');
  const department = searchParams.get('dept') || 'N/A';
  const domainName = searchParams.get('domain') || 'N/A';
  const submissionTimestamp = searchParams.get('time') || new Date().toISOString();

  const breadcrumbs = (
    <>
      <Link href="/support/" className="text-[#1a679f] font-semibold hover:underline">Web Hosting Support</Link>
      <span className="mx-2">»</span>
      <Link href="/" className="text-[#1a679f] font-semibold hover:underline">Contact GlowHost Sales</Link>
      <span className="mx-2">»</span>
      <span>Ticket: {referenceId}</span>
    </>
  );

  return (
    <MainLayout breadcrumbs={breadcrumbs}>
      <TicketThread
        referenceId={referenceId}
        customerName={customerName}
        email={email}
        subject={subject}
        message={message}
        department={department}
        domainName={domainName}
        submissionTimestamp={submissionTimestamp}
      />
    </MainLayout>
  );
};

const SupportThreadPageWrapper = () => (
    <Suspense fallback={<div>Loading...</div>}>
        <SupportThreadPage />
    </Suspense>
);

export default SupportThreadPageWrapper;
