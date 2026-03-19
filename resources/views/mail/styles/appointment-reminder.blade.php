<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; }
    .wrapper { width: 100%; background-color: #f4f4f5; padding: 32px 16px; }
    .card { max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
    .header { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); padding: 36px 40px 32px; text-align: center; }
    .header-icon { display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; background: rgba(255,255,255,0.2); border-radius: 14px; margin-bottom: 16px; font-size: 28px; }
    .header h1 { color: #ffffff; font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
    .header p { color: rgba(255,255,255,0.8); font-size: 14px; margin-top: 6px; }
    .body { padding: 36px 40px; }
    .label { font-size: 11px; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; color: #6b7280; margin-bottom: 6px; }
    .appointment-title { font-size: 24px; font-weight: 700; color: #111827; letter-spacing: -0.4px; margin-bottom: 28px; line-height: 1.3; }
    .details { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; }
    .detail-row { display: flex; align-items: flex-start; gap: 14px; padding: 10px 0; }
    .detail-row + .detail-row { border-top: 1px solid #e5e7eb; }
    .detail-icon { font-size: 16px; line-height: 1.5; flex-shrink: 0; width: 20px; text-align: center; }
    .detail-content { flex: 1; }
    .detail-key { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.6px; color: #9ca3af; margin-bottom: 2px; }
    .detail-value { font-size: 14px; color: #374151; font-weight: 500; }
    .reminder-badge { display: inline-flex; align-items: center; gap: 6px; background: #fef3c7; border: 1px solid #fde68a; border-radius: 999px; padding: 6px 14px; font-size: 13px; font-weight: 600; color: #92400e; margin-bottom: 28px; }
    .cta-wrapper { text-align: center; margin-bottom: 8px; }
    .cta-button { display: inline-block; background: #16a34a; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 32px; border-radius: 10px; letter-spacing: 0.1px; }
    .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 40px; text-align: center; }
    .footer p { font-size: 12px; color: #9ca3af; line-height: 1.6; }
    .footer a { color: #6b7280; text-decoration: underline; }
    @media (max-width: 600px) {
        .header, .body { padding: 28px 24px; }
        .footer { padding: 20px 24px; }
        .appointment-title { font-size: 20px; }
    }
</style>
