<?php

// database/migrations/2026_05_02_000001_create_pitch_monitor_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | pitch_monitor_sessions
        |--------------------------------------------------------------------------
        |
        | Stores one real-time pitch monitoring session per authenticated user.
        | This table is separate from guitar_sessions because Pitch Monitor is
        | general-purpose and not limited to guitar string tuning.
        |
        */
        DB::statement("
            CREATE TABLE IF NOT EXISTS public.pitch_monitor_sessions (
                session_id BIGSERIAL PRIMARY KEY,

                user_id BIGINT NOT NULL,

                source_type VARCHAR(100) NOT NULL DEFAULT 'microphone'
                    CHECK (source_type IN ('microphone', 'uploaded_audio', 'test_signal')),

                started_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
                    DEFAULT timezone('Asia/Manila', now()),

                ended_at TIMESTAMP WITHOUT TIME ZONE NULL,

                created_at TIMESTAMP WITHOUT TIME ZONE NULL
                    DEFAULT timezone('Asia/Manila', now()),

                updated_at TIMESTAMP WITHOUT TIME ZONE NULL
                    DEFAULT timezone('Asia/Manila', now())
            );
        ");

        /*
        |--------------------------------------------------------------------------
        | pitch_monitor_events
        |--------------------------------------------------------------------------
        |
        | Stores each detected pitch event inside one Pitch Monitor session.
        | Each event contains detected note, frequency, cents deviation,
        | confidence, RMS signal level, tuning status, and detection time.
        |
        */
        DB::statement("
            CREATE TABLE IF NOT EXISTS public.pitch_monitor_events (
                event_id BIGSERIAL PRIMARY KEY,

                session_id BIGINT NOT NULL,

                note_name VARCHAR(8) NOT NULL
                    CHECK (note_name ~ '^[A-G](#|b)?-?[0-9]$'),

                frequency NUMERIC(10, 2) NOT NULL
                    CHECK (frequency >= 20 AND frequency <= 5000),

                cents_deviation NUMERIC(8, 2) NULL
                    CHECK (
                        cents_deviation IS NULL
                        OR (cents_deviation >= -100 AND cents_deviation <= 100)
                    ),

                confidence NUMERIC(8, 4) NULL
                    CHECK (
                        confidence IS NULL
                        OR (confidence >= 0 AND confidence <= 1)
                    ),

                rms NUMERIC(10, 5) NULL
                    CHECK (
                        rms IS NULL
                        OR (rms >= 0 AND rms <= 10)
                    ),

                tuning_status VARCHAR(20) NOT NULL DEFAULT 'detected'
                    CHECK (tuning_status IN ('flat', 'in_tune', 'sharp', 'detected')),

                detected_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
                    DEFAULT timezone('Asia/Manila', now()),

                created_at TIMESTAMP WITHOUT TIME ZONE NULL
                    DEFAULT timezone('Asia/Manila', now()),

                updated_at TIMESTAMP WITHOUT TIME ZONE NULL
                    DEFAULT timezone('Asia/Manila', now())
            );
        ");

        /*
        |--------------------------------------------------------------------------
        | Foreign Keys
        |--------------------------------------------------------------------------
        |
        | These are recreated safely to guarantee ON DELETE CASCADE.
        | This prevents orphan pitch events when a session is deleted.
        |
        */
        DB::statement("
            ALTER TABLE public.pitch_monitor_sessions
            DROP CONSTRAINT IF EXISTS pitch_monitor_sessions_user_id_foreign;
        ");

        DB::statement("
            ALTER TABLE public.pitch_monitor_sessions
            ADD CONSTRAINT pitch_monitor_sessions_user_id_foreign
            FOREIGN KEY (user_id)
            REFERENCES public.user_account(user_id)
            ON DELETE CASCADE;
        ");

        DB::statement("
            ALTER TABLE public.pitch_monitor_events
            DROP CONSTRAINT IF EXISTS pitch_monitor_events_session_id_foreign;
        ");

        DB::statement("
            ALTER TABLE public.pitch_monitor_events
            ADD CONSTRAINT pitch_monitor_events_session_id_foreign
            FOREIGN KEY (session_id)
            REFERENCES public.pitch_monitor_sessions(session_id)
            ON DELETE CASCADE;
        ");

        /*
        |--------------------------------------------------------------------------
        | Indexes
        |--------------------------------------------------------------------------
        |
        | These improve session lookup, history loading, and event ordering.
        |
        */
        DB::statement("
            CREATE INDEX IF NOT EXISTS pitch_monitor_sessions_user_id_index
            ON public.pitch_monitor_sessions(user_id);
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS pitch_monitor_sessions_started_at_index
            ON public.pitch_monitor_sessions(started_at);
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS pitch_monitor_sessions_user_open_index
            ON public.pitch_monitor_sessions(user_id, ended_at);
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS pitch_monitor_events_session_id_index
            ON public.pitch_monitor_events(session_id);
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS pitch_monitor_events_detected_at_index
            ON public.pitch_monitor_events(detected_at);
        ");

        DB::statement("
            CREATE INDEX IF NOT EXISTS pitch_monitor_events_session_detected_index
            ON public.pitch_monitor_events(session_id, detected_at);
        ");

        DB::statement("
            CREATE OR REPLACE FUNCTION public.set_pitch_monitor_updated_at_ph_time()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.updated_at = timezone('Asia/Manila', now());
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ");

        DB::statement("
            DROP TRIGGER IF EXISTS set_pitch_monitor_sessions_updated_at
            ON public.pitch_monitor_sessions;
        ");

        DB::statement("
            CREATE TRIGGER set_pitch_monitor_sessions_updated_at
            BEFORE UPDATE ON public.pitch_monitor_sessions
            FOR EACH ROW
            EXECUTE FUNCTION public.set_pitch_monitor_updated_at_ph_time();
        ");

        DB::statement("
            DROP TRIGGER IF EXISTS set_pitch_monitor_events_updated_at
            ON public.pitch_monitor_events;
        ");

        DB::statement("
            CREATE TRIGGER set_pitch_monitor_events_updated_at
            BEFORE UPDATE ON public.pitch_monitor_events
            FOR EACH ROW
            EXECUTE FUNCTION public.set_pitch_monitor_updated_at_ph_time();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * Warning:
     * - This will remove Pitch Monitor data if rollback is executed.
     * - Do not run rollback unless you intentionally want to remove this module.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER IF EXISTS set_pitch_monitor_events_updated_at ON public.pitch_monitor_events;");
        DB::statement("DROP TRIGGER IF EXISTS set_pitch_monitor_sessions_updated_at ON public.pitch_monitor_sessions;");

        DB::statement("DROP TABLE IF EXISTS public.pitch_monitor_events;");
        DB::statement("DROP TABLE IF EXISTS public.pitch_monitor_sessions;");

        DB::statement("DROP FUNCTION IF EXISTS public.set_pitch_monitor_updated_at_ph_time();");
    }
};