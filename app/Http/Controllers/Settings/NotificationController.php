<?php

namespace App\Http\Controllers\Settings;

use App\Enums\NotificationType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function edit(Request $request)
    {
        $settings = $request->user()->notificationSettings->keyBy('notification_type');

        $notificationTypes = collect(NotificationType::cases())->map(function ($type) use ($settings) {
            return [
                'type' => $type->value,
                'label' => $type->label(),
                'is_enabled' => $settings->has($type->value) ? $settings->get($type->value)->is_enabled : false,
            ];
        });

        return Inertia::render('settings/Notifications', [
            'notificationTypes' => $notificationTypes,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'is_enabled' => 'required|boolean',
        ]);

        $type = NotificationType::from($validated['type']);

        $request->user()->notificationSettings()->updateOrCreate(
            ['notification_type' => $type->value],
            ['is_enabled' => $validated['is_enabled']]
        );

        return back()->with('success', 'Benachrichtigungseinstellungen aktualisiert');
    }
}
