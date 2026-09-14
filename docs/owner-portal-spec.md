# Owner Portal Spec — FastNetStays Unified (Portal → Web → Mobile)

Single source: `fastnet_backend` Laravel API. All three shells consume same contracts.

## Base
- Env: `BACKEND_API_URL` (`admin_owner_portal/.env`, `web/config/app.php App.backendApiUrl`, `fastnet_mobile_front_end/lib/services/api_service.dart baseUrl`)
- Auth: `Authorization: Bearer <token>` (portal `$_SESSION[api_token]` `admin_owner_portal/config/dz.php:17`, web `auth_token` session `AccountController.php:36`, mobile `SharedPreferences api_token` `api_service.dart:25`)

## Contracts (used by all shells)
| Capability | Endpoint | Method | Used by |
|---|---|---|---|
| List properties | `/api/properties` | GET `?city=&price_max=` | portal `index.php:12`, web `StaysController.php:182`, mobile `fetchProperties` |
| Rooms per property | `/api/properties/{id}/rooms` | GET | web `rooms.php`, portal `room-list.php:52` |
| Create property | `/api/properties` | POST `{name,description,address,city,area,price_per_night,lat,long,image_url}` | mobile `createProperty` `add_property.dart:173`, portal onboarding |
| Create room | `/api/properties/{id}/rooms` | POST `{room_number,room_type_id,price,capacity,amenities[],photos[]}` | portal `room-list.php:14` |
| List all rooms | `/api/rooms` | GET | portal `room-list.php:52` |
| List bookings (guest) | `/api/bookings` | GET `?email=` or auth | web `AccountController.php:190`, mobile `fetchBookings` |
| Admin bookings | `/api/admin/bookings` | GET | portal `guest-list.php:10` |
| Bookings calc | `/api/bookings/calculate` | POST | web `BookingQuoteService.php:39` |
| Payments checkout/status | `/api/payments/checkout`, `/api/payments/status/{id}` | POST/GET | web `BookingsController.php:263`, mobile `checkoutPayment` |
| Admin properties | `/api/admin/properties` | GET | portal `index.php:12` |
| Admin users owners | `/api/admin/users?role=owner` | GET | portal `ecom-customers.php:36` |
| Verification owner/lodge | `/api/admin/verification/owner/{id}`, `/api/admin/verification/lodge/{id}` | POST `{status,reason}` | portal `ecom-customers.php:16` |
| Finance overview | `/api/finance/overview?date_range` | GET | portal `chart-chartist.php:262`, `chart-flot.php:251` |
| Payouts | `/api/payouts?status=` `POST /api/payouts/request` `PATCH /api/payouts/{id}/status` | GET/POST/PATCH | portal `chart-flot.php:329` |
| Staff | `/api/staff` | GET/POST/PATCH/DELETE | mobile `fetchStaff` `api_service.dart:426` |
| Messages | `/api/messages/threads`, `/api/messages/{partnerId}`, `/api/messages` | GET/POST | mobile `host_messages.dart`, portal `email-compose.php:21` |
| Wishlist | `/api/wishlist` | GET/POST/DELETE | web `AccountController.php:306` |

## Roles
- `admin` sees all (`admin_owner_portal/index.php:14` filter bypassed, mobile no admin view, web none yet)
- `owner` filtered `host_id == userId` (portal `index.php:14`, `room-list.php:44`)

## Design Tokens (Phases 1-4)
- Radius `r16` cards, `r14` chips/pills, `r22` mobile cards, `r30` buttons
- Shadow `0 6 16 rgba(0,0,0,0.05)` / `0 4 12 rgba(0,0,0,0.04)`
- Colors `#2563EB` primary, `#C2410C` price, `#F8FAFC`/`#F0F3FF` bg, `#EBF5FF` selected, `#15803d` success, `#e8eaed` border
- Portal previously `#135846` → migrate to above

## Migration Plan (Phases)
- P0 spec (this doc)
- P1 `web/src/Controller/HostController.php` + `config/routes.php` `/host/*` (dashboard/listings/create/edit/bookings/calendar/earnings) → reuse `FastnetApiClient`
- P2 listings `add-room.php:52` dashed `#135846` → `r12 #e8eaed`, mobile `add_property.dart:350`
- P3 bookings/earnings unify
- P4 QA lint `php -l`, `flutter analyze`
