# API

> [!CAUTION]
> This documentation is incomplete – and may be incorrect …

The RPA process overview does not show data directly from the source … we fetch it and transform it …

A `GET` request to `/group/11/overview/24/data?page=1&size=5`, say, will return JSON data in the following form[^1]:

``` json5
{
  // The main data
  data: {
    // The columns
    columns: [
      {
        label: "Borger",
        data: "metadata.cpr",
        type: "text"
      },
      {
        label: "Name",
        data: "metadata.name",
        type: "text"
      },
      {
        label: "Klinik",
        data: "metadata.branch",
        type: "text"
      },
      {
        label: "American.",
        type: "step"
      },
      {
        label: "Surface later.",
        type: "step"
      },
      {
        label: "Apply.",
        type: "step"
      },
      {
        label: "Office wall.",
        type: "step"
      },
      {
        label: "Tree back.",
        type: "step"
      },
      {
        label: "Mrs others.",
        type: "step"
      }
    ],
    // The rows
    rows: [
      [
        {
          type: "text",
          value: null
        },
        {
          type: "text",
          value: null
        },
        {
          type: "text",
          value: null
        },
        {
          created_at: "2025-09-30T08:31:29",
          started_at: "2025-09-14T02:47:44.613627",
          failure: null,
          step_id: 8,
          id: 176,
          updated_at: "2025-09-30T08:31:29",
          status: "SUCCESS",
          finished_at: "2025-09-14T02:47:51.613627",
          run_id: 26,
          step_index: 0,
          type: "step"
        },
        {
          created_at: "2025-09-30T08:31:29",
          started_at: "2025-09-14T02:56:05.613627",
          failure: {
            code: 6,
            message: "Southern white plan campaign.",
            retryable: true,
            occurred_at: "2025-09-14T02:56:14.613627"
          },
          step_id: 9,
          id: 177,
          updated_at: "2025-09-30T08:31:29",
          status: "FAILED",
          finished_at: null,
          run_id: 26,
          step_index: 1,
          type: "step"
        },
        {
          created_at: "2025-09-30T08:31:29",
          started_at: null,
          failure: null,
          step_id: 10,
          id: 178,
          updated_at: "2025-09-30T08:31:29",
          status: "PENDING",
          finished_at: null,
          run_id: 26,
          step_index: 2,
          type: "step"
        },
        {
          created_at: "2025-09-30T08:31:29",
          started_at: null,
          failure: null,
          step_id: 11,
          id: 179,
          updated_at: "2025-09-30T08:31:29",
          status: "PENDING",
          finished_at: null,
          run_id: 26,
          step_index: 3,
          type: "step"
        },
        {
          created_at: "2025-09-30T08:31:29",
          started_at: null,
          failure: null,
          step_id: 12,
          id: 180,
          updated_at: "2025-09-30T08:31:29",
          status: "PENDING",
          finished_at: null,
          run_id: 26,
          step_index: 4,
          type: "step"
        },
        {
          created_at: "2025-09-30T08:31:29",
          started_at: null,
          failure: null,
          step_id: 13,
          id: 181,
          updated_at: "2025-09-30T08:31:29",
          status: "PENDING",
          finished_at: null,
          run_id: 26,
          step_index: 5,
          type: "step"
        }
      ],
      …
    ]
  },
  // Pagination URLs
  links: {
    self: "https://rpa-process-overview.local.itkdev.dk/group/11/overview/24/data?page=1&size=5",
    next: "https://rpa-process-overview.local.itkdev.dk/group/11/overview/24/data?page=2&size=5"
  },
  meta: {
    // The total number of rows
    total: 12
  }
}
```

[^1]: We present the data as [JSON5](https://json5.org) for improved readability.
