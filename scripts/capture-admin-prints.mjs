import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = process.env.AURA_BASE_URL ?? 'http://127.0.0.1:8000';
const email = process.env.AURA_ADMIN_EMAIL ?? 'admin@aura.local';
const password = process.env.AURA_ADMIN_PASSWORD ?? 'Admin@12345';
const outputDir = path.resolve('public', 'images', 'aura-prints');

const captures = [
    {
        file: 'chamados.png',
        paths: ['/admin/tickets'],
        readySelector: 'text=Chamados',
    },
    {
        file: 'suprimentos.png',
        paths: ['/admin/suprimentos', '/admin/stock-items'],
        readySelector: 'text=Suprimentos',
    },
    {
        file: 'faturamento.png',
        paths: ['/admin/invoices'],
        readySelector: 'text=Faturas',
    },
];

async function goToFirstValidPath(page, paths) {
    for (const routePath of paths) {
        const response = await page.goto(`${baseUrl}${routePath}`, { waitUntil: 'domcontentloaded' });

        if (!response) {
            continue;
        }

        const status = response.status();

        if (status >= 200 && status < 400) {
            return routePath;
        }
    }

    throw new Error(`Nenhuma rota valida encontrada entre: ${paths.join(', ')}`);
}

async function run() {
    await fs.mkdir(outputDir, { recursive: true });

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1600, height: 980 },
    });
    const page = await context.newPage();

    await page.goto(`${baseUrl}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input#form\\.email', email);
    await page.fill('input#form\\.password', password);

    await page.getByRole('button', { name: 'Entrar' }).click();

    const loginStartedAt = Date.now();

    while (new URL(page.url()).pathname.endsWith('/admin/login')) {
        if (Date.now() - loginStartedAt > 20_000) {
            const debugPath = path.join(outputDir, 'login-debug.png');
            await page.screenshot({ path: debugPath, fullPage: true });
            throw new Error(`Falha ao autenticar no painel. Screenshot de depuração: ${debugPath}`);
        }

        await page.waitForTimeout(300);
    }

    for (const capture of captures) {
        const resolvedPath = await goToFirstValidPath(page, capture.paths);
        await page.waitForSelector(capture.readySelector, { timeout: 15_000 });
        await page.waitForTimeout(1200);

        const filePath = path.join(outputDir, capture.file);
        await page.screenshot({
            path: filePath,
            fullPage: false,
        });

        console.log(`OK ${capture.file} <- ${resolvedPath}`);
    }

    await context.close();
    await browser.close();
}

run().catch((error) => {
    console.error(error);
    process.exit(1);
});
