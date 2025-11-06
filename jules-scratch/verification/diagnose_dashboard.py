from playwright.sync_api import sync_playwright, Page, expect
import re

def diagnose_dashboard(page: Page):
    """
    This script navigates to the dashboard and captures console errors
    to help diagnose integration issues.
    """
    # 1. Listen for console errors
    console_errors = []
    page.on("console", lambda msg: console_errors.append(msg.text) if msg.type == "error" else None)

    # 2. Navigate to the dashboard page.
    page.goto("http://localhost:4200/dashboard", timeout=60000)

    # 3. Wait for the page to be in a stable state.
    # We'll wait for the main filter container to be visible.
    try:
        expect(page.get_by_role("heading", name="Filtros")).to_be_visible(timeout=15000)
        print("Dashboard page loaded successfully.")
    except Exception as e:
        print(f"Error waiting for page to load: {e}")

    # 4. Take a screenshot for visual inspection.
    page.screenshot(path="/app/jules-scratch/verification/diagnosis.png")
    print("Screenshot taken at /app/jules-scratch/verification/diagnosis.png")

    # 5. Report any console errors found.
    if console_errors:
        print("\n--- CONSOLE ERRORS DETECTED ---")
        for error in console_errors:
            print(error)
        print("-----------------------------\n")
    else:
        print("\nNo console errors were detected.\n")


# --- Playwright Boilerplate ---
def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        diagnose_dashboard(page)
        browser.close()

if __name__ == "__main__":
    run()