export function formatPKR(amount: number): string {
  return 'Rs. ' + amount.toLocaleString('en-PK');
}

export function formatDate(dateString: string): string {
  try {
    const d = new Date(dateString);
    return d.toLocaleDateString('en-PK', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return dateString;
  }
}

export function cleanPhoneForWhatsApp(phone: string): string {
  const digits = phone.replace(/[^0-9]/g, '');
  if (digits.startsWith('03')) {
    return '92' + digits.substring(1);
  }
  if (digits.startsWith('92')) {
    return digits;
  }
  return '92' + digits;
}
