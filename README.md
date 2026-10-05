# QR Code Check

QR Code Check is a Moodle activity for recording attendance or participation through a short-lived QR code displayed by a teacher. Each projection is treated as an independent attendance session, while Moodle capabilities control who can manage sessions, view reports and register participation.

## How it works

- The projected QR code changes every **10 seconds** and each generated code is accepted for at most **20 seconds**.
- A valid scan reaches `scan.php` and is persisted before authentication, so a student who still needs to sign in does not lose the original scan.
- After a valid anonymous scan, the student receives a one-time continuation that remains valid for up to **15 minutes** and is bound to the same anonymous browser session.
- The teacher or projector IP address is saved when a projection session starts.
- The report compares the IP observed during the student's scan with the session reference IP and highlights records made from a different IP.
- Only the first confirmed attendance record for each student in each session is kept.
- Old pending scans are removed automatically by the scheduled task.

## Activity and completion

The activity uses Moodle's standard activity settings and includes a custom completion rule that can mark the activity complete after the student successfully registers a QR scan. Teachers with the appropriate capabilities can start and end projection sessions and access the attendance report.

## Backup and restore

Course backup, restore, import and activity duplication preserve QR Code Check sessions and attendance records when user data is included. Historical restored sessions are closed and receive a new secret, so an old projected QR code cannot become valid again after restoration.

## Privacy

The plugin stores the scan time, the student's IP address and the reference IP address for the projection session. These data are declared through Moodle's Privacy API.

## Screenshots

![QR Code Check](https://raw.githubusercontent.com/EduardoKrausME/marketplace-plugins/master/screenshots/mod_qrcodecheck/new-1.png)

![QR Code Check activity](https://raw.githubusercontent.com/EduardoKrausME/marketplace-plugins/master/screenshots/mod_qrcodecheck/new-2.png)
