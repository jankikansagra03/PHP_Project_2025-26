(function () {
  var STORAGE_KEY = "jk_store_theme_tokens_v1";
  var defaults = {
    "--theme-primary": "#1f7a8c",
    "--theme-secondary": "#bf5af2",
    "--theme-accent": "#ff7a59",
    "--theme-surface": "#ffffff",
    "--theme-text": "#1f2937",
    "--theme-bg-start": "#0f172a",
    "--theme-bg-end": "#1f2937",
  };

  var presets = {
    lagoon: {
      "--theme-primary": "#1f7a8c",
      "--theme-secondary": "#bf5af2",
      "--theme-accent": "#ff7a59",
      "--theme-surface": "#ffffff",
      "--theme-text": "#1f2937",
      "--theme-bg-start": "#0f172a",
      "--theme-bg-end": "#1f2937",
    },
    sunset: {
      "--theme-primary": "#ef4444",
      "--theme-secondary": "#f59e0b",
      "--theme-accent": "#f97316",
      "--theme-surface": "#fff7ed",
      "--theme-text": "#3f1d0b",
      "--theme-bg-start": "#7c2d12",
      "--theme-bg-end": "#b45309",
    },
    forest: {
      "--theme-primary": "#166534",
      "--theme-secondary": "#22c55e",
      "--theme-accent": "#84cc16",
      "--theme-surface": "#f7fee7",
      "--theme-text": "#1f2937",
      "--theme-bg-start": "#14532d",
      "--theme-bg-end": "#3f6212",
    },
    ocean: {
      "--theme-primary": "#0ea5e9",
      "--theme-secondary": "#2563eb",
      "--theme-accent": "#14b8a6",
      "--theme-surface": "#f0f9ff",
      "--theme-text": "#0f172a",
      "--theme-bg-start": "#0b2447",
      "--theme-bg-end": "#1d4ed8",
    },
    midnight: {
      "--theme-primary": "#0f172a",
      "--theme-secondary": "#334155",
      "--theme-accent": "#a855f7",
      "--theme-surface": "#f8fafc",
      "--theme-text": "#0f172a",
      "--theme-bg-start": "#020617",
      "--theme-bg-end": "#1e293b",
    },
    coral: {
      "--theme-primary": "#fb7185",
      "--theme-secondary": "#f97316",
      "--theme-accent": "#eab308",
      "--theme-surface": "#fff7f3",
      "--theme-text": "#3f1f1f",
      "--theme-bg-start": "#7f1d1d",
      "--theme-bg-end": "#be123c",
    },
    grape: {
      "--theme-primary": "#7c3aed",
      "--theme-secondary": "#a855f7",
      "--theme-accent": "#f472b6",
      "--theme-surface": "#faf5ff",
      "--theme-text": "#312e81",
      "--theme-bg-start": "#3b0764",
      "--theme-bg-end": "#581c87",
    },
    neon: {
      "--theme-primary": "#06b6d4",
      "--theme-secondary": "#3b82f6",
      "--theme-accent": "#22c55e",
      "--theme-surface": "#ecfeff",
      "--theme-text": "#0f172a",
      "--theme-bg-start": "#082f49",
      "--theme-bg-end": "#0f766e",
    },
    ember: {
      "--theme-primary": "#dc2626",
      "--theme-secondary": "#ea580c",
      "--theme-accent": "#f59e0b",
      "--theme-surface": "#fff7ed",
      "--theme-text": "#431407",
      "--theme-bg-start": "#7f1d1d",
      "--theme-bg-end": "#9a3412",
    },
    mint: {
      "--theme-primary": "#10b981",
      "--theme-secondary": "#14b8a6",
      "--theme-accent": "#22d3ee",
      "--theme-surface": "#ecfdf5",
      "--theme-text": "#064e3b",
      "--theme-bg-start": "#064e3b",
      "--theme-bg-end": "#0f766e",
    },
    rosewood: {
      "--theme-primary": "#be123c",
      "--theme-secondary": "#e11d48",
      "--theme-accent": "#fb7185",
      "--theme-surface": "#fff1f2",
      "--theme-text": "#4c0519",
      "--theme-bg-start": "#4c0519",
      "--theme-bg-end": "#881337",
    },
    denim: {
      "--theme-primary": "#1d4ed8",
      "--theme-secondary": "#2563eb",
      "--theme-accent": "#38bdf8",
      "--theme-surface": "#eff6ff",
      "--theme-text": "#1e3a8a",
      "--theme-bg-start": "#1e3a8a",
      "--theme-bg-end": "#1d4ed8",
    },
    aurora: {
      "--theme-primary": "#06b6d4",
      "--theme-secondary": "#8b5cf6",
      "--theme-accent": "#ec4899",
      "--theme-surface": "#f5f3ff",
      "--theme-text": "#312e81",
      "--theme-bg-start": "#164e63",
      "--theme-bg-end": "#7e22ce",
    },
    sand: {
      "--theme-primary": "#d97706",
      "--theme-secondary": "#f59e0b",
      "--theme-accent": "#65a30d",
      "--theme-surface": "#fffbeb",
      "--theme-text": "#78350f",
      "--theme-bg-start": "#78350f",
      "--theme-bg-end": "#a16207",
    },
    slate: {
      "--theme-primary": "#334155",
      "--theme-secondary": "#475569",
      "--theme-accent": "#0ea5e9",
      "--theme-surface": "#f8fafc",
      "--theme-text": "#0f172a",
      "--theme-bg-start": "#0f172a",
      "--theme-bg-end": "#1e293b",
    },
    kiwi: {
      "--theme-primary": "#65a30d",
      "--theme-secondary": "#84cc16",
      "--theme-accent": "#facc15",
      "--theme-surface": "#f7fee7",
      "--theme-text": "#365314",
      "--theme-bg-start": "#365314",
      "--theme-bg-end": "#4d7c0f",
    },
    plum: {
      "--theme-primary": "#7e22ce",
      "--theme-secondary": "#9333ea",
      "--theme-accent": "#c084fc",
      "--theme-surface": "#faf5ff",
      "--theme-text": "#581c87",
      "--theme-bg-start": "#3b0764",
      "--theme-bg-end": "#6b21a8",
    },
    steel: {
      "--theme-primary": "#0f766e",
      "--theme-secondary": "#0ea5e9",
      "--theme-accent": "#6366f1",
      "--theme-surface": "#f0fdfa",
      "--theme-text": "#134e4a",
      "--theme-bg-start": "#134e4a",
      "--theme-bg-end": "#164e63",
    },
    embernight: {
      "--theme-primary": "#f97316",
      "--theme-secondary": "#ef4444",
      "--theme-accent": "#8b5cf6",
      "--theme-surface": "#fff7ed",
      "--theme-text": "#431407",
      "--theme-bg-start": "#431407",
      "--theme-bg-end": "#7f1d1d",
    },
    skyglass: {
      "--theme-primary": "#38bdf8",
      "--theme-secondary": "#06b6d4",
      "--theme-accent": "#22d3ee",
      "--theme-surface": "#f0f9ff",
      "--theme-text": "#0c4a6e",
      "--theme-bg-start": "#082f49",
      "--theme-bg-end": "#155e75",
    },
    bronze: {
      "--theme-primary": "#b45309",
      "--theme-secondary": "#92400e",
      "--theme-accent": "#f59e0b",
      "--theme-surface": "#fffbeb",
      "--theme-text": "#451a03",
      "--theme-bg-start": "#451a03",
      "--theme-bg-end": "#78350f",
    },
    jade: {
      "--theme-primary": "#047857",
      "--theme-secondary": "#10b981",
      "--theme-accent": "#34d399",
      "--theme-surface": "#ecfdf5",
      "--theme-text": "#064e3b",
      "--theme-bg-start": "#022c22",
      "--theme-bg-end": "#065f46",
    },
    ruby: {
      "--theme-primary": "#e11d48",
      "--theme-secondary": "#fb7185",
      "--theme-accent": "#f43f5e",
      "--theme-surface": "#fff1f2",
      "--theme-text": "#4c0519",
      "--theme-bg-start": "#4c0519",
      "--theme-bg-end": "#9f1239",
    },
    graphite: {
      "--theme-primary": "#1f2937",
      "--theme-secondary": "#374151",
      "--theme-accent": "#6b7280",
      "--theme-surface": "#f9fafb",
      "--theme-text": "#111827",
      "--theme-bg-start": "#111827",
      "--theme-bg-end": "#1f2937",
    },
  };
  var presetOrder = [
    "lagoon",
    "sunset",
    "forest",
    "ocean",
    "midnight",
    "coral",
    "grape",
    "neon",
    "ember",
    "mint",
    "rosewood",
    "denim",
    "aurora",
    "sand",
    "slate",
    "kiwi",
    "plum",
    "steel",
    "embernight",
    "skyglass",
    "bronze",
    "jade",
    "ruby",
    "graphite",
  ];
  var INLINE_COLOR_MAP = {
    "#667eea": "var(--theme-primary)",
    "#764ba2": "var(--theme-secondary)",
    "#0d9488": "var(--theme-primary)",
    "#d4af37": "var(--theme-accent)",
    "#24324a": "var(--theme-text)",
    "#3b4ec9": "var(--theme-secondary)",
    "#283b84": "var(--theme-secondary)",
    "#f4f7ff": "var(--theme-surface)",
    "#f8faff": "var(--theme-surface)",
  };

  function getSaved() {
    try {
      var raw = localStorage.getItem(STORAGE_KEY);
      return raw ? JSON.parse(raw) : {};
    } catch (e) {
      return {};
    }
  }

  function saveTheme(tokens) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(tokens));
  }

  function applyTheme(tokens) {
    var root = document.documentElement;
    Object.keys(tokens).forEach(function (k) {
      root.style.setProperty(k, tokens[k]);
    });
  }

  function hydrateInputs(tokens) {
    var inputs = document.querySelectorAll("input[data-theme-var]");
    inputs.forEach(function (input) {
      var key = input.getAttribute("data-theme-var");
      if (tokens[key]) {
        input.value = tokens[key];
      }
    });
  }

  function titleCase(text) {
    return text.charAt(0).toUpperCase() + text.slice(1);
  }

  function renderPresetButtons() {
    var list = document.getElementById("themePresetList");
    if (!list) return;

    list.innerHTML = "";
    presetOrder.forEach(function (name) {
      var preset = presets[name];
      if (!preset) return;

      var button = document.createElement("button");
      button.type = "button";
      button.className = "theme-preset";
      button.setAttribute("data-theme-preset", name);
      button.setAttribute("title", titleCase(name));

      var swatches = document.createElement("span");
      swatches.className = "theme-preset-swatches";
      [
        preset["--theme-primary"],
        preset["--theme-secondary"],
        preset["--theme-accent"],
      ].forEach(function (color) {
        var dot = document.createElement("span");
        dot.className = "swatch";
        dot.style.backgroundColor = color;
        swatches.appendChild(dot);
      });

      var label = document.createElement("span");
      label.className = "theme-preset-name";
      label.textContent = titleCase(name);

      button.appendChild(swatches);
      button.appendChild(label);
      list.appendChild(button);
    });
  }

  function replaceColorTokens(input) {
    var output = input;
    Object.keys(INLINE_COLOR_MAP).forEach(function (key) {
      var matcher = new RegExp(key.replace("#", "\\#"), "gi");
      output = output.replace(matcher, INLINE_COLOR_MAP[key]);
    });
    return output;
  }

  function normalizeHardcodedInlineColors() {
    var styledNodes = document.querySelectorAll("[style]");
    styledNodes.forEach(function (node) {
      var styleText = node.getAttribute("style");
      if (!styleText) return;
      var replaced = replaceColorTokens(styleText);
      if (replaced !== styleText) {
        node.setAttribute("style", replaced);
      }
    });

    var styleBlocks = document.querySelectorAll("style");
    styleBlocks.forEach(function (styleBlock) {
      var cssText = styleBlock.textContent || "";
      if (!cssText) return;
      var replaced = replaceColorTokens(cssText);
      if (replaced !== cssText) {
        styleBlock.textContent = replaced;
      }
    });
  }

  function tokensMatch(base, candidate) {
    return Object.keys(defaults).every(function (k) {
      return (
        (base[k] || "").toLowerCase() === (candidate[k] || "").toLowerCase()
      );
    });
  }

  function syncActivePreset(tokens) {
    var presetButtons = document.querySelectorAll(
      ".theme-preset[data-theme-preset]",
    );
    presetButtons.forEach(function (button) {
      button.classList.remove("active");
      var presetName = button.getAttribute("data-theme-preset");
      if (
        presetName &&
        presets[presetName] &&
        tokensMatch(tokens, presets[presetName])
      ) {
        button.classList.add("active");
      }
    });
  }

  function composeTokens() {
    var fromStorage = getSaved();
    return Object.assign({}, defaults, fromStorage);
  }

  function bindPanel() {
    var fab = document.getElementById("themeFab");
    var panel = document.getElementById("themePanel");
    var closeBtn = document.getElementById("themePanelClose");
    var resetBtn = document.getElementById("themeReset");

    if (!fab || !panel) return;

    fab.addEventListener("click", function () {
      panel.classList.toggle("open");
      panel.setAttribute(
        "aria-hidden",
        panel.classList.contains("open") ? "false" : "true",
      );
    });

    if (closeBtn) {
      closeBtn.addEventListener("click", function () {
        panel.classList.remove("open");
        panel.setAttribute("aria-hidden", "true");
      });
    }

    document.addEventListener("click", function (e) {
      if (!panel.classList.contains("open")) return;
      if (panel.contains(e.target) || fab.contains(e.target)) return;
      panel.classList.remove("open");
      panel.setAttribute("aria-hidden", "true");
    });

    var inputs = document.querySelectorAll("input[data-theme-var]");
    inputs.forEach(function (input) {
      input.addEventListener("input", function () {
        var tokens = composeTokens();
        var key = input.getAttribute("data-theme-var");
        tokens[key] = input.value;
        applyTheme(tokens);
        saveTheme(tokens);
        syncActivePreset(tokens);
      });
    });

    var presetButtons = document.querySelectorAll(
      ".theme-preset[data-theme-preset]",
    );
    presetButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        var presetName = button.getAttribute("data-theme-preset");
        if (!presetName || !presets[presetName]) return;
        var tokens = Object.assign({}, composeTokens(), presets[presetName]);
        applyTheme(tokens);
        saveTheme(tokens);
        hydrateInputs(tokens);
        syncActivePreset(tokens);
      });
    });

    if (resetBtn) {
      resetBtn.addEventListener("click", function () {
        localStorage.removeItem(STORAGE_KEY);
        applyTheme(defaults);
        hydrateInputs(defaults);
        syncActivePreset(defaults);
      });
    }
  }

  var tokens = composeTokens();
  applyTheme(tokens);

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function () {
      renderPresetButtons();
      normalizeHardcodedInlineColors();
      hydrateInputs(tokens);
      syncActivePreset(tokens);
      bindPanel();
    });
  } else {
    renderPresetButtons();
    normalizeHardcodedInlineColors();
    hydrateInputs(tokens);
    syncActivePreset(tokens);
    bindPanel();
  }
})();
