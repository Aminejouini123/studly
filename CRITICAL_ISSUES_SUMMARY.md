# 🚨 CRITICAL ISSUES - QUICK REFERENCE

## ⛔ BLOCKERS - DO NOT DEPLOY UNTIL FIXED

### 1. CSRF Protection Disabled
**File:** `config/packages/security.yaml:25`
**Issue:** `enable_csrf: false`
**Fix:** Change to `enable_csrf: true`
**Risk:** CRITICAL - Application vulnerable to CSRF attacks

### 2. Unvalidated Registration
**File:** `src/Controller/Auth/AuthController.php:30-71`
**Issue:** Direct `$request->request->get()` usage, no form validation
**Fix:** Use Symfony Form with UserType
**Risk:** CRITICAL - Injection attacks, data corruption

### 3. File Upload Security
**Files:** Multiple controllers (CoursesController, ActivityController, ExamenController)
**Issues:**
- No file type validation
- No file size limits
- Files in public directory
- No filename sanitization
**Fix:** Create FileUploadService with validation
**Risk:** CRITICAL - Malicious file uploads, path traversal

### 4. Direct Request Parameter Access
**Files:** AuthController, PomodoroController, TempsController, GroupsController
**Issue:** `$request->request->get()` / `$request->query->get()` without validation
**Fix:** Use Forms or DTOs
**Risk:** HIGH - Injection attacks, type errors

### 5. Missing Authorization Checks
**Files:** CoursesController, ActivityController
**Issue:** Manual checks instead of `#[IsGranted]` attributes
**Fix:** Add `#[IsGranted]` to all protected routes
**Risk:** HIGH - Unauthorized access

---

## 🔧 QUICK FIXES (Copy-Paste Ready)

### Fix CSRF Protection
```yaml
# config/packages/security.yaml
form_login:
    enable_csrf: true  # Change from false
    csrf_parameter: '_csrf_token'
    csrf_token_id: 'authenticate'
```

### Fix Registration Endpoint
```php
// src/Controller/Auth/AuthController.php
#[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
public function register(
    Request $request,
    UserPasswordHasherInterface $userPasswordHasher,
    EntityManagerInterface $entityManager,
    UserRepository $userRepository
): Response {
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        if ($userRepository->findOneBy(['email' => $user->getEmail()])) {
            $this->addFlash('error', 'Email already registered.');
            return $this->render('auth/register.html.twig', ['form' => $form]);
        }
        
        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPlainPassword()));
        $user->setRoles(['ROLE_ETUDIANT']);
        $user->setStatut('Active');
        
        $entityManager->persist($user);
        $entityManager->flush();
        
        return $this->redirectToRoute('app_auth');
    }
    
    return $this->render('auth/register.html.twig', ['form' => $form]);
}
```

### Add Authorization to CoursesController
```php
#[Route('/courses', name: 'app_courses')]
#[IsGranted('ROLE_USER')]
public function index(CourseRepository $courseRepository): Response
{
    // ... existing code
}

#[Route('/course/new', name: 'app_course_new', methods: ['GET', 'POST'])]
#[IsGranted('ROLE_USER')]
public function new(...): Response
{
    // ... existing code
}
```

---

## 📋 CHECKLIST BEFORE DEPLOYMENT

- [ ] CSRF protection enabled
- [ ] All forms use Symfony Form component
- [ ] File uploads validated (type, size, filename)
- [ ] All protected routes have `#[IsGranted]` attributes
- [ ] No direct `$request->request->get()` usage
- [ ] Input validation on all endpoints
- [ ] File uploads moved outside public or access-controlled
- [ ] Database indexes added
- [ ] N+1 queries fixed
- [ ] Error logging implemented
- [ ] Security headers configured
- [ ] Password strength requirements added

---

**See `CODE_AUDIT_REPORT.md` for complete detailed analysis.**
