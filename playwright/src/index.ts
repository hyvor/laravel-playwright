import {APIRequestContext, test as playwrightTest} from '@playwright/test';

export interface LaravelOptions {
    /**
     * The base URL to the endpoints
     * @default <playwright-base-url>/playwright
     */
    laravelBaseUrl: string | undefined;
}

interface LaravelFixtures {
    laravel: Laravel;
}

export const test = playwrightTest.extend<LaravelFixtures & LaravelOptions>({

    laravelBaseUrl: [undefined, {option: true}],

    laravel: async ({ laravelBaseUrl, baseURL, request }, use) => {
        const baseUrl = laravelBaseUrl || baseURL + '/playwright'
        const laravel = new Laravel(baseUrl, request);
        await use(laravel);
        await laravel.tearDown();
    }

})

export class Laravel {

    constructor(
        private baseUrl: string,
        private request: APIRequestContext
    ) {}

    async call<T extends any>(endpoint: string, data: object = {}) : Promise<T> {
        const url = this.baseUrl.replace(/\/$/, '') + endpoint;
        const response = await this.request.post(url, {data});
        if (response.status() !== 200) {
            throw new Error(`
                Failed to call Laravel ${endpoint}. 
                Status: ${response.status()}
                Response: ${await response.text()}
            `);
        }

        return await response.json();
    }

    async artisan(command: string, parameters: string[] = []) {
        return await this.call<{code: number, output: string}>('/artisan', {command, parameters});
    }

    async truncate(connections: (string|null)[] = []) {
        return await this.call('/truncate', {connections});
    }

    async factory<CountT extends number | undefined = undefined>(
        model: string,
        attrs: any = {},
        count: CountT = undefined as CountT
    ) {
        return await this.call<CountT extends undefined ? Record<string, any> : Record<string, any>[]>('/factory', {model, count, attrs});
    }

    async query(
        query: string,
        bindings: Array<any> = [],
        options: {
            connection?: string | null,
            unprepared?: boolean
        } = {}
    ) {

        const { connection = null, unprepared = false } = options;

        if (unprepared && bindings.length > 0) {
            throw new Error('Cannot use unprepared with bindings');
        }

        return await this.call<{
            success: boolean
        }>('/query', {
            query,
            bindings,
            connection,
            unprepared
        });

    }

    async select(
        query: string,
        bindings: Record<string, any> = {},
        options: {
            connection?: string | null,
            unprepared?: boolean
        } = {}
    ) {
        const { connection = null } = options;
        return await this.call<Record<string, any>[]>('/select', {query, bindings, connection});
    }

    async callFunction<T extends any>(func: string, args: any[]|Record<string, any> = []) {
        return await this.call<T>('/function', {function: func, args});
    }

    /**
     * Sets a laravel config value until tearDown is called (or the test ends)
     */
    async config(key: string, value: any) {
        return await this.call('/dynamicConfig', {key, value});
    }

    /**
     * Travel to a specific time
     * ex: travel('2021-01-01 00:00:00')
     */
    async travel(to: string) {
        return await this.call('/travel', {to});
    }

    async registerBootFunction(func: string) {
        return await this.call('/registerBootFunction', {function: func});
    }

    async tearDown() {
        return await this.call('/tearDown');
    }

}