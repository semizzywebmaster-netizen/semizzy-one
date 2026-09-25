/* ============================================
   SEMIZZY ONE — Form Data-Loss Protection
   ============================================ */

import { useState, useEffect, useCallback, useRef } from 'react';
import { offlineDb } from '@/lib/offlineDb';
import { generateId } from '@/lib/utils';

interface FormProtectionOptions {
    formId: string;
    url: string;
    autoSaveIntervalMs?: number;
    enableBeforeUnload?: boolean;
}

export function useFormProtection(options: FormProtectionOptions) {
    const { formId, url, autoSaveIntervalMs = 30000, enableBeforeUnload = true } = options;
    const [hasDraft, setHasDraft] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const [idempotencyKey] = useState(() => generateId());
    const formDataRef = useRef<Record<string, unknown>>({});

    // Check for existing draft on mount
    useEffect(() => {
        offlineDb.getDraft(formId).then((draft) => {
            if (draft) setHasDraft(true);
        });
    }, [formId]);

    // Auto-save draft periodically
    useEffect(() => {
        if (!isDirty) return;

        const interval = setInterval(() => {
            if (Object.keys(formDataRef.current).length > 0) {
                offlineDb.saveDraft(formId, url, formDataRef.current);
            }
        }, autoSaveIntervalMs);

        return () => clearInterval(interval);
    }, [isDirty, formId, url, autoSaveIntervalMs]);

    // Prevent accidental navigation
    useEffect(() => {
        if (!enableBeforeUnload || !isDirty) return;

        const handler = (e: BeforeUnloadEvent) => {
            e.preventDefault();
            e.returnValue = '';
        };

        window.addEventListener('beforeunload', handler);
        return () => window.removeEventListener('beforeunload', handler);
    }, [isDirty, enableBeforeUnload]);

    const updateFormData = useCallback((data: Record<string, unknown>) => {
        formDataRef.current = { ...formDataRef.current, ...data };
        setIsDirty(true);
    }, []);

    const restoreDraft = useCallback(async () => {
        const draft = await offlineDb.getDraft(formId);
        if (draft) {
            formDataRef.current = draft.data;
            setHasDraft(false);
            return draft.data;
        }
        return null;
    }, [formId]);

    const clearDraft = useCallback(async () => {
        await offlineDb.deleteDraft(formId);
        formDataRef.current = {};
        setIsDirty(false);
        setHasDraft(false);
    }, [formId]);

    const getIdempotencyKey = useCallback(() => idempotencyKey, [idempotencyKey]);

    return {
        hasDraft,
        isDirty,
        updateFormData,
        restoreDraft,
        clearDraft,
        getIdempotencyKey,
    };
}

export default useFormProtection;